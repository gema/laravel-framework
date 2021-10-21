@section('preload')
<link rel="preload" href="{{ aurl('uploads', $slides[0]->image) }}" as="image" />
@endsection

<div class="slider">
    <div class="flex-slider" swipeable auto-scroll="5000">
        <ul class="pages">
            @foreach ($slides as $slide)
            <li class="slide" style="background-image: url({{ aurl('uploads', $slide->image) }});">
                <div class="container slides">
                    <div class="content">
                        <div class="overflow">
                            <div class="blur" style="background-image: url({{ aurl('uploads', $slide->image) }});">
                            </div>
                        </div>
                        <div class="excerpt">
                            <h2>{{ $slide->title }}</h2>
                            <p>{{ Str::limit($slide->description, 300) }}</p>
                            @if($slide->url)
                            <a class="knowmore" href="{{ $slide->url }}" target="_blank">{{ __('ui.knowmore') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        <ul class="dots">
            @foreach($slides as $slide)
            <li class="{{ $loop->iteration === 1 ? 'active' : '' }}"></li>
            @endforeach
        </ul>
    </div>
</div>