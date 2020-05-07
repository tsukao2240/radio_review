{{ csrf_field() }}
{{ Form::open(['route' => 'search','method' => 'get']) }}
<div class="mx-auto" style="width:400">
    <div class="input-group">
        {{ Form::text('title','',['class' => 'form-control','placeholder' => '番組名で検索する']) }}
        {{ Form::button('<i class="fas fa-search"></i>',['class' => 'btn','type' => 'submit']) }}
    </div>
</div>
{{ Form::close() }}
