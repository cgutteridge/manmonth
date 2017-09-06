@extends('page')

@section('title')
    @title($documentRevision->document) rev #{{$documentRevision->id}} ({{$documentRevision->status}})
@endsection

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 style="margin: 0">Revision Data</h2>
        </div>
        <div class="row panel-body">
            <div class="col-md-12">
                <p>
                    @if($status=='scrap')
                        This revision has been scrapped.
                    @elseif( $status=='draft')
                        This is the current draft revision.
                    @elseif( $status=='archive')
                        @if($latest)
                            This is the most recent revision.
                        @endif
                        @if($latest_published)
                            This is the most recent published revision.
                        @elseif($published)
                            This revision has been published, but there is a more recent published revision.
                        @else
                            This revision has been committed, but not made public.
                        @endif
                    @else
                        This revision has an unknown status: '{{$status}}'.
                    @endif
                </p>
                <p>
                    Created @datetime( $created_at )
                </p>
                @if($documentRevision->status == 'archive')
                    @can('publish',$documentRevision->document)
                        @if( $documentRevision->published )
                            <a type="button" class="btn btn-primary" href="@url($documentRevision,'unpublish')">
                                Unpublish revision
                            </a>
                        @else
                            <a type="button" class="btn btn-primary" href="@url($documentRevision,'publish')">
                                Publish revision
                            </a>
                        @endif
                    @endcan
                @endif
                @if($documentRevision->status == 'draft')
                    @can('commit', $documentRevision->document)
                        <a type="button" class="btn btn-primary"
                           href="@url($documentRevision,'commit-and-continue')">
                            Commit and make a new draft revision
                        </a>
                        <a type="button" class="btn btn-primary"
                           href="@url($documentRevision,'commit')">
                            Commit revision
                        </a>
                        <a type="button" class="btn btn-primary"
                           href="@url($documentRevision,'scrap')">
                            Scrap revision
                        </a>
                        @can('publish',$documentRevision->document)
                            <a type="button" class="btn btn-primary"
                               href="@url($documentRevision,'commit-and-publish')">
                                Commit and publish revision
                            </a>
                            @endcan

                            @endcan
                            @endif
                            </p>

            </div>
            <div class="col-md-6">
                <h3>Records</h3>
                <ul>
                    @foreach( $documentRevision->recordTypes as $recordType )
                        @if( !$recordType->isProtected() )
                            <li>
                                <a href="@url( $recordType, 'records' )">@title($recordType)</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="col-md-6">
                <h3>Report Types</h3>
                <ul>
                    @foreach( $documentRevision->reportTypes as $reportType )
                        <li>
                            <a href="@url($reportType)">@title($reportType)</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 style="margin:0">Revision Schema</h2>
        </div>
        <div class="row panel-body">
            <div class="col-md-6">
                <h3>Record Types</h3>
                <ul>
                    @foreach( $documentRevision->recordTypes as $recordType )
                        <li>
                            @link($recordType)
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-6">
                <h3>Link Types</h3>
                <ul>
                    @foreach( $documentRevision->linkTypes as $linkType )
                        <li>
                            @link($linkType)
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
