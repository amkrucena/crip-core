<?php namespace Crip\Core\Events;

use Crip\Core\Contracts\ICripObject;
use Crip\Core\Data\Model;
use Crip\Core\Exceptions\BadEventResultException;
use Crip\Core\Support\Help;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

/**
 * Class EventCollector
 * @package Crip\Core\Events
 */
class EventCollector implements ICripObject
{

    /**
     * @var array
     */
    private $event_results = null;

    /**
     * @var array
     */
    private $event_names = null;

    /**
     * Push event results to collection
     *
     * @param array $event_results
     * @param string $name
     * @return $this
     */
    protected function push(array $event_results, $name = false)
    {
        $this->event_results = array_merge($this->event_results, $event_results);

        if ($name) {
            $this->event_names[] = $name;
        }

        return $this;
    }

    /**
     * Clear event results collection
     *
     * @return $this
     */
    protected function clearEvents()
    {
        $this->event_results = [];
        $this->event_names = [];

        return $this;
    }

    /**
     * Get all collected events
     *
     * @return array
     */
    protected function events()
    {
        return $this->event_results;
    }

    /**
     * @return array
     */
    protected function eventNames()
    {
        return $this->event_names;
    }

    /**
     * @param $event_method
     * @param Request $request
     * @param array $input
     * @param bool|int $update
     * @param Model $instance
     *
     * @return bool|\Illuminate\Http\JsonResponse
     *
     * @throws BadEventResultException
     */
    protected function validateOnEvents(
        $event_method,
        Request $request,
        array $input,
        $update = false,
        Model $instance = null
    ) {
        $fails = false;
        $errors = new MessageBag();

        $event_collector = $this->call($event_method, $request, $input, $update, $instance);

        /** @var Validator $validator */
        foreach ($event_collector->events() as $validator) {
            $this->validateEventResponse($validator, $event_collector);

            if ($validator->fails()) {
                $fails = true;
                $errors->merge($validator->getMessageBag()->toArray());
            }
        }

        if ($fails) {
            return response()->json($errors, 422);
        }

        return null;
    }

    /**
     * @param $event_method
     * @param Request $request
     * @param array $input
     * @param bool|int $update
     * @param Model $instance
     *
     * @return EventCollector
     */
    private function call($event_method, Request $request, array $input, $update = false, Model $instance = null)
    {
        $params = [$request, $input];
        // If it is update action, id should be as first parameter in method
        // and existing record model representation at the end
        if ($update !== false) {
            array_unshift($params, $update);
            $params[] = $instance;
        }

        return call_user_func_array([$this, $event_method], $params);
    }

    /**
     * @param $response
     * @param EventCollector $collector
     * @throws BadEventResultException
     */
    private function validateEventResponse($response, EventCollector $collector)
    {
        if (!Help::isInstanceOf(Validator::class, $response)) {
            $events = $collector->eventNames();
            throw new BadEventResultException($this, $events, Validator::class);
        }
    }
}