<?php

namespace App\Classes\Traits;

use Illuminate\Http\Request;

use App\Http\Requests;

trait ApiRespondable
{
	protected $statusCode = 200;

	protected $CODE_NOT_FOUND 		= 'ERROR_NOT_FOUND';
	protected $CODE_WRONG_ARGS		= 'ERROR_WRONG_ARGS';
	protected $CODE_INTERNAL_ERROR	= 'ERROR_INTERNAL';
	protected $CODE_UNAUTHORIZED		= 'ERROR_UNAUTHORIZED';
	protected $CODE_FORBIDDEN		= 'ERROR_FORBIDDEN';

	/**
	 * Getter for statusCode
	 * @return int
	 */
	protected function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * Setter for statusCode
	 * @param int $statusCode Value to set
	 *
	 * @return self
	 */
	protected function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * Making json response from array
	 * 
	 * @param  array  $array to respond with
	 * @param  array  $headers to attach
	 * @return mixed - json
	 */
	protected function respondWithArray(array $array, array $headers = []) {
		return response()->json($array, $this->statusCode, $headers);
	}


	/**
	 * Return success response with message.
	 * 	
	 * @param  mixed $message
	 * @return mixed - json
	 */
    protected function respondWithSuccess($message) {
        if ($this->statusCode !== 200) {
            trigger_error(
                "You shall not success with code other than 200...",
                E_USER_WARNING
            );
        }
        return $this->respondWithArray([
            'success'   =>  [
                'message'   =>  $message
            ]
        ]);
    }

	/**
	 * Return error response with message
	 * 
	 * @param  mixed $message error response
	 * @param  string $errorCode from codes predifined at the top
	 * @return mixed - json
	 */
	protected function respondWithError($message, $errorCode)
	{
		if($this->statusCode == 200) {
			trigger_error(
                "Erroring with status code 200? WAT?",
                E_USER_WARNING
            );
		}

		return $this->respondWithArray([
			"error" => [
				"code" 		=>	$errorCode,
				"http_code"	=>	$this->statusCode,
				"message"	=>	$message	
			]
		]);
	}

	 /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message, $this->CODE_FORBIDDEN);
    }
    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message, $this->CODE_INTERNAL_ERROR);
    }
    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message, $this->CODE_NOT_FOUND);
    }
    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)->respondWithError($message, $this->CODE_UNAUTHORIZED);
    }
    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message, $this->CODE_WRONG_ARGS);
    }
}
