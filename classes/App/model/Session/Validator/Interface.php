<?php

interface App_Session_Validator_Interface
{

    /**
     * Setup() - this method will store the environment variables
     * necessary to be able to validate against in future requests.
     *
     * @return void
     */
    public function setup();

    /**
     * Validate() - this method will be called at the beginning of
     * every session to determine if the current environment matches
     * that which was store in the setup() procedure.
     *
     * @return boolean
     */
    public function validate();

}
