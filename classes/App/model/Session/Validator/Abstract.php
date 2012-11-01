<?php

abstract class App_Session_Validator_Abstract implements App_Session_Validator_Interface
{

    /**
     * SetValidData() - This method should be used to store the environment variables that
     * will be needed in order to validate the session later in the validate() method.
     * These values are stored in the session in the __ZF namespace, in an array named VALID
     *
     * @param  mixed $data
     * @return void
     */
    protected function setValidData($data)
    {
        $validatorName = get_class($this);

        $_SESSION['__WCF']['VALID'][$validatorName] = $data;
    }


    /**
     * GetValidData() - This method should be used to retrieve the environment variables that
     * will be needed to 'validate' a session.
     *
     * @return mixed
     */
    protected function getValidData()
    {
        $validatorName = get_class($this);

        return $_SESSION['__WCF']['VALID'][$validatorName];
    }

}
