<?php namespace Crip\Core\Events;

use Crip\Core\Contracts\ICripObject;
use Crip\Core\Exceptions\BadEventResultException;
use Crip\Core\Support\Help;
use Illuminate\Contracts\Validation\Validator;
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

    public function __construct()
    {
        $this->event_results = [];
        $this->event_names = [];
    }

    /**
     * Push event results to collection
     *
     * @param array $results
     * @param string|bool $name
     *
     * @return $this
     */
    public function push(array $results, $name = false)
    {
        $this->event_results = array_merge($this->event_results, $results);

        if ($name) {
            $this->event_names[] = $name;
        }

        return $this;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null
     * @throws BadEventResultException
     */
    public function asValidator()
    {
        $fails = false;
        $errors = new MessageBag();

        /** @var Validator $validator */
        foreach($this->events() as $validator) {
            $this->validateValidationEventResponse($validator);

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
     * @param $validator
     * @throws BadEventResultException
     */
    private function validateValidationEventResponse($validator)
    {
        if (!Help::isInstanceOf(Validator::class, $validator)) {
            $events = $this->eventNames();
            throw new BadEventResultException($this, $events, Validator::class);
        }
    }

}