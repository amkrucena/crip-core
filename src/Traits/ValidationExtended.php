<?php namespace Crip\Core\Traits;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

/**
 * Class ValidationExtended
 * @package Crip\Core\Traits
 */
trait ValidationExtended
{
    /**
     * Throe exception if $fails
     *
     * @param bool $fails
     * @param Request $request
     * @param MessageBag $errors
     * @return bool|JsonResponse
     */
    protected function validationException($fails, Request $request, MessageBag $errors)
    {
        if ($fails) {
            return $this->errorResponse($request, $errors);
        }

        return null;
    }

    /**
     * @param Request $request
     * @param MessageBag $errors
     * @return $this|JsonResponse
     */
    protected function errorResponse(Request $request, MessageBag $errors)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse($errors, 422);
        }

        return redirect()->to($this->getRedirectUrl())
            ->withInput($request->input())
            ->withErrors($errors, $errors);
    }

    /**
     * Get the URL we should redirect to.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        return app(UrlGenerator::class)->previous();
    }

}