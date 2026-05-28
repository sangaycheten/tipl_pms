<?php

namespace App\Exceptions;

use Throwable;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException as HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException as QueryException;
use Illuminate\Session\TokenMismatchException as TokenMismatchException;
use ErrorException;
use Auth;
use App\ErrorLog as ErrorLog;
use Log;
use App\Http\Controllers\Controller as Controller;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
//        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,

    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
            switch ($statusCode) {
                case 404:
                    $this->saveError($e, true);
                    return response()->view('errors.404', [
                        'content' => view('errors.404')
                    ], 404);
            }
        }
        if ($e instanceof TokenMismatchException) {
            return back()->with('reload', true);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return redirect('/');
        }
        if ($e instanceof HttpException || $e instanceof ErrorException || $e instanceof QueryException || $this->isHttpException($e)) {
            $this->saveError($e);
            return response()->view('errors.500', [
                'content' => view('errors.500')
            ], 500);
        }
        return parent::render($request, $e);
    }
    public function saveError(\Throwable $e, $is404 = false){
        $errorDesc = "Error Code: ".($is404?'404':$e->getCode());
        $errorDesc.= "<br/>Error Message: ".($is404?'Page not found':$e->getMessage());
        $errorDesc.= "<br/>File: ".$e->getFile();
        $errorDesc.= "<br/>Line No.: ".$e->getLine();
        $errorDesc.= "<br/>URL: ".urldecode($_SERVER['REQUEST_URI']);
        $errorDesc.= "<br/>POST VARS: ".arrayToString($_POST);
        $errorDesc.= "<br/>GET VARS: ".arrayToString($_GET);
        $errorDesc.= "<br/>User Id: ".(isset(Auth::user()->Id)?Auth::user()->Id:'guest');
        $errorDesc.= "<br/>Trace: ".$e->getTraceAsString();

        $error['Id'] = UUID();
        $error['File'] = $e->getFile();
        $error['LineNo'] = $e->getLine();
        $error['Description'] = $errorDesc;
        $error['Message'] = ($is404?'Page not found':$e->getMessage());
        $error['URL'] = urldecode($_SERVER['REQUEST_URI']);
        if(strstr($error['URL'],'/assets/plugins/')==false || strstr($error['URL'],'/assets/plugins/') < 0){
            $error['Code'] = ($is404?'404':$e->getCode());
            $error['Date'] = date('Y-m-d H:i:s');

            if($error['Code'] != '404'){
                $object = new Controller;
                $mailBody = $errorDesc;
//		$object->sendMail('web.mis@tashicell.com',$mailBody,'PMS Online: Error on '.date('Y-m-d H:i:s'));
		$object->sendSMS(77116699,'PMS Online: Error on '.date('Y-m-d H:i:s'));
 		$object->sendSMS(77106699,'PMS Online: Error on '.date('Y-m-d H:i:s'));
            }

            ErrorLog::create($error);
        }
    }
}

