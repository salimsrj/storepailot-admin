<?php

namespace App\AI\Tools;

use App\AI\Agent\AgentContext;
use App\AI\DTOs\ToolCall;
use App\Exceptions\InvalidToolArgumentsException;
use App\Exceptions\ToolExecutionException;
use App\Support\RequestId;
use Illuminate\Support\Facades\Log;
use Throwable;

class ToolExecutor
{
    public function __construct(private ToolRegistry $registry) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AgentContext $context, ToolCall $call): array
    {
        $tool = $this->registry->find($call->name, $context);

        if ($tool === null) {
            throw InvalidToolArgumentsException::forTool($call->name);
        }

        try {
            $result = $tool->execute($context, $call->arguments);
        } catch (InvalidToolArgumentsException $exception) {
            throw $exception;
        } catch (Throwable) {
            Log::channel('ai')->warning('tool.failed', [
                'request_id' => RequestId::current(),
                'site_id' => $context->site->id,
                'conversation_id' => $context->conversation->id,
                'tool_name' => $call->name,
                'status' => 'error',
            ]);

            throw ToolExecutionException::failed($call->name);
        }

        Log::channel('ai')->info('tool.executed', [
            'request_id' => RequestId::current(),
            'site_id' => $context->site->id,
            'conversation_id' => $context->conversation->id,
            'tool_name' => $call->name,
            'status' => 'ok',
        ]);

        return $result;
    }
}
