@extends('adminlte::page')

@section('title', 'Queue')

@section('content_header')
    <h1>Queue</h1>
@stop

@section('content')
    <queues></queues>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#jobsTable').DataTable({
                retrieve: true,
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 50,
                ajax: `/campaigns/jobs`,
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'queue', name: 'queue' },
                    { data: 'status', name: 'status' },
                    { data: 'payload', name: 'payload' },
                ]
            });
            $('#failedJobsTable').DataTable({
                retrieve: true,
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 50,
                ajax: `/campaigns/failed-jobs`,
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'queue', name: 'queue' },
                    { data: 'status', name: 'status' },
                    { data: 'payload', name: 'payload' },
                    { data: 'exception', name: 'exception' },
                ]
            });
        });
    </script>
@stop