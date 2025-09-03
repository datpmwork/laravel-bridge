<?php

namespace Bref\LaravelBridge\Console;

use Psy\CodeCleaner\NoReturnValue;
use Psy\Shell;

class LambdaShell extends Shell
{
    protected $contextRestored = false;

    public function setScopeVariables(array $vars)
    {
        parent::setScopeVariables($vars);

        // Only write new context data when the context was restored
        if ($this->contextRestored) {
            $excludedSpecialVars = array_diff($this->getScopeVariables(false), $this->getSpecialScopeVariables(false));
            $this->writeContextData($excludedSpecialVars);
        }
    }

    /**
     * @param  $context
     * @return void
     */
    public function writeContextData($vars)
    {
        $context = base64_encode(serialize($vars));

        $this->writeStdout("[CONTEXT]{$context}[END_CONTEXT]");
    }

    /**
     * @param  $context
     * @return void
     */
    public function writeReturnValueData($ret)
    {
        if ($ret instanceof NoReturnValue) {
            return;
        }

        $prompt = '= ';
        $indent = \str_repeat(' ', \strlen($prompt));
        $formatted = $this->presentValue($ret);
        $formattedRetValue = \sprintf('<whisper>%s</whisper>', $prompt);
        $formatted = $formattedRetValue.\str_replace(\PHP_EOL, \PHP_EOL.$indent, $formatted);
        $this->writeStdout("[RETURN]{$formatted}[END_RETURN]");
    }

    public function restoreContextData($context)
    {
        if ($returnVars = unserialize(base64_decode($context))) {
            $this->setScopeVariables($returnVars);
        }
    }

    /**
     * @param $contextRestored
     * @return $this
     */
    public function setContextRestored($contextRestored)
    {
        $this->contextRestored = $contextRestored;

        return $this;
    }
}
