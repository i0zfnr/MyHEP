MyHEP system admin alert

New report #{{ $report['id'] }}: {{ $report['subject'] }}
Category: {{ str_replace('_', ' ', $report['category']) }}
Reporter: {{ $report['reporter_name'] }} ({{ $report['reporter_email'] }})
Page: {{ $report['page_url'] ?: 'Not provided' }}
Screenshot: {{ $report['has_screenshot'] ? 'Attached to the internal report' : 'Not provided' }}

{{ $report['description'] }}

Review the report: {{ route('admin.bug-reports.index') }}
