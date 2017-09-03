<?php

namespace App\Traits;

use Illuminate\Support\MessageBag;

/**
 * Class modelValidate
 * @package e1\Traits
 *
 * @property MessageBag $errors
 * @property string $_scenario
 * @property array $data
 */
trait ModelValidate
{
    public $errors;
    public $_scenario;

    public $data = [];
    protected $event = [];

    public $customRules = [];
    protected $scenarioRules = [];
    protected $customMessages = [];
    protected $defaultValues = [];

    /**
     * modelValidate constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->errors = new MessageBag();
    }

    public function toArray($error_scenario = false)
    {
        if ($error_scenario) {
            $this->setAttribute('errors', $this->errors);
            $this->setAttribute('_scenario', $this->_scenario);
        }

        return parent::toArray();
    }

    /**
     * Validate current model by rules() in model class
     *
     * @param array $data
     * @param bool $load
     * @return bool
     */
    public function validate(array $data = [], $load = true): bool
    {
        /**
         * @var \Illuminate\Validation\Factory $validator
         * @var array $rules
         * @var \Illuminate\Validation\Validator $error
         */

        if (empty($this->_scenario)) {
            $this->setScenario();
        }

        $rules = $this->rules();
        $validator = $this->app('validator');

        # set default data
        foreach ($this->defaultValues as $field => $defaultValue) {
//            if (!isset($this->attributes[$field])) {
                $this->setAttribute($field, array_get($data, $field, $defaultValue));
//            }
        }

        # merge new values and new
        $this->data = array_merge($this->getAttributes(), $data);

        # add rules by scenario
        if ($this->_scenario) {
            $rules = $this->getRulesWithScenario($this->_scenario, $rules);
        }

        # add custom validate rules
        foreach ($this->customRules as $name => $callable) {
            $validator->extend($name, $callable, $this->customMessages[$name] ?? null);
        }

        # create a new Validator instance
        $error = $validator->make($this->data, $rules, $this->customMessages);

        # add custom after validate callback
        if (isset($this->event['after']) && is_callable($this->event['after'])) {
            $error->after($this->event['after']);
        }

        # run validate
        if ($error->fails()) {

            # load error message to error class
            $validateErrors = $error->errors()->toArray();
            foreach ($validateErrors as $name => $errors) {
                foreach ($errors as $error) {
                    $this->errors->add($name, $error);
                }
            }
            return false;

        } elseif ($load && !empty($data)) {
            # load if valid and not empty
            # if list scenario (for search)
            if($this->isScenario([self::SCENARIO_LIST])){
                $this->setRawAttributes($data) ;
            } else {
                $this->fill($data);
            }
        }
        return true;
    }

    public function getRulesWithScenario(string $scenario, array $rules)
    {
        foreach ($this->getScenarioRules($scenario) as $attribute => $scenarioRule) {

            $rulesField = [];
            $attributeRules = [];

            if ($scenarioRule) {
                $attributeRules = is_string($scenarioRule) ? explode('|', $scenarioRule) : $scenarioRule;
            }

            if (isset($rules[$attribute])) {
                $rulesField = is_string($rules[$attribute]) ? explode('|', $rules[$attribute]) : $rules[$attribute];
            }

            $rules[$attribute] = array_merge($attributeRules, $rulesField);
        }

        return $rules;
    }

    public function getData(string $name, $default = null)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : $default;
    }


    /**
     * Run before get Rules
     *
     * @return array
     */
    public function custom()
    {
        return [];
    }

    /**
     * Return rules array for validate
     * @return array
     */
    public function rules(): array
    {
        return [];
    }


    /**
     * Set current scenario for validate
     *
     * @param string $scenario
     * @return $this
     */
    public function setScenario(string $scenario = '')
    {
        $this->_scenario = empty($scenario) ? $this->exists ? 'update' : 'create' : $scenario;

        return $this;
    }

    public function isScenario(array $scenarios)
    {
        return in_array($this->_scenario, $scenarios, false);
    }


    /**
     * Get rules for scenario
     *
     * @param string $scenario
     * @return array
     */
    public function getScenarioRules(string $scenario): array
    {
        return $this->scenarioRules[$scenario] ?? [];
    }


    /**
     * Add custom rule for this object validate
     *
     * @param string $name
     * @param callable $callable
     */
    protected function addEvent(string $name, callable $callable)
    {
        $this->customMessages[$name] = $callable;
    }

    /**
     * Add custom rule for this object validate
     *
     * @param string $name
     * @param callable $callable
     * @param string $message
     */
    protected function addRule(string $name, callable $callable, string $message = '')
    {
        $this->customRules[$name] = $callable;

        if (!empty($message)) {
            $this->customMessages[$name] = $message;
        }
    }

    /**
     * @return MessageBag
     */
    public function getErrorsAttribute()
    {
        return $this->errors;
    }
}