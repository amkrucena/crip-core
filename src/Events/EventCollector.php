<?php namespace Crip\Core\Events;

use Crip\Core\Contracts\ICripObject;
use Crip\Core\Exceptions\BadEventResultException;
use Crip\Core\Support\Help;
use Crip\Core\Traits\ValidationExtended;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

/**
 * Class EventCollector
 * @package Crip\Core\Events
 */
class EventCollector implements ICripObject
{
    use ValidationExtended;

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
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws BadEventResultException
     */
    protected function validateOnEvents($event_method, Request $request, array $input)
    {
        $fails = false;
        $errors = new MessageBag();

        $event_collector = $this->call($event_method, $request, $input);
        /** @var Validator $validator */
        foreach ($event_collector->events() as $validator) {
            $this->validateEventResponse($validator, $event_collector);

            if ($validator->fails()) {
                $fails = true;
                $errors->merge($validator->getMessageBag()->toArray());
            }
        }

        return $this->validationException($fails, $request, $errors);
    }

    /**
     * @param $event_method
     * @param Request $request
     * @param array $input
     * @return EventCollector
     */
    private function call($event_method, Request $request, array $input)
    {
        return call_user_func_array([$this, $event_method], [$request, $input]);
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