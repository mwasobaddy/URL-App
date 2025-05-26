<x-mail::message>
# Revenue Report

Your requested revenue report for the period **{{ $startDate }}** to **{{ $endDate }}** is attached.

## Available Formats

The report is available in the following formats:

@foreach($files as $file)
* [{{ strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)) }}]({{ $file['url'] }})
@endforeach

These download links will expire in 24 hours.

<x-mail::button :url="$files[0]['url']">
Download Excel Report
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
