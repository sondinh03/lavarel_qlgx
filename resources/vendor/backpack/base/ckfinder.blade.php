@extends('backpack::layouts.top_left')

@section('after_scripts')
    <script type="text/javascript" src="{{ asset('/js/ckfinder/ckfinder.js') }}"></script>
    <script>CKFinder.config( { connectorPath: '{{ route('ckfinder_connector') }}' } );</script>

    <script>
        CKFinder.widget('ckfinder-widget', {
            width: '100%',
            height: 'calc(100vh - 150px)'
        });
    </script>
@endsection

@section('content')
    <div id="ckfinder-widget"></div>
@endsection
