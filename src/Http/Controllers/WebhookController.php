<?php

namespace OpenAI\Laravel\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use OpenAI\Laravel\Events\WebhookReceived;
use OpenAI\Laravel\Http\Requests\WebhookRequest;

class WebhookController extends Controller
{
    public function __invoke(WebhookRequest $request, Dispatcher $dispatcher): Response
    {
        $dispatcher->dispatch(
            new WebhookReceived(
                $request->getEventType(),
                $request->getEventId(),
                $request->getTimestamp(),
                $request->getData(),
            ),
        );

        return response()->noContent(202);
    }
}
