<?php

return [
    'conversation_retention_days' => max(1, (int) env('AI_CONVERSATION_RETENTION_DAYS', 30)),
];
