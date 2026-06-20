<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI co-thinker (via the shared LiteLLM gateway)
    |--------------------------------------------------------------------------
    |
    | Roots Factory talks to the same LiteLLM gateway as the rest of the
    | wendland-cloud stack. We only ever address a logical role alias
    | (e.g. "wendland-smart"); which real provider/model sits behind it is
    | decided centrally in litellm/config.yaml — never here.
    |
    */

    'base_url' => env('LITELLM_BASE_URL', 'http://litellm:4000/v1'),

    'key' => env('LITELLM_MASTER_KEY'),

    // Logical role alias, not a concrete model. Analysis quality -> smart.
    'model' => env('ROOTSFACTORY_AI_MODEL', 'wendland-smart'),

    'timeout' => (int) env('ROOTSFACTORY_AI_TIMEOUT', 90),

    // Auto-post a summary when an idea moves to "in_discussion".
    'auto_summary' => (bool) env('ROOTSFACTORY_AI_AUTO_SUMMARY', true),

    // Identity the co-thinker posts under in discussions.
    'author' => [
        'email' => env('ROOTSFACTORY_AI_EMAIL', 'ai@rootsfactory.org'),
        'name' => env('ROOTSFACTORY_AI_NAME', 'Roots Factory AI'),
    ],
];
