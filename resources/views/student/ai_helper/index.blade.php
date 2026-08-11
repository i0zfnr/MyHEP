@php
    $studentAiMode = true;
@endphp
@include('admin.ai_helper.index', compact('studentAiMode', 'aiEnabled', 'aiProvider', 'aiModel'))
