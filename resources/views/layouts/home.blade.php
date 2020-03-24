<h1>Home</h1>
<table class="table">
    <thead>
        <tr>
            <th>station</th>
            <th>title</th>
            <th>time</th>
            <th>url</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $result)
            <tr>
                <td>{{$result['station']}}</td>
                <td>{{$result['title']}}</td>
                <td>{{$result['start'] . '-' . $result['end']}}</td>
                <a href="http://"></a>
                <td><a href="{{$result['url']}}">公式ホームページ</a></td>
            </tr>
    @endforeach
    </tbody>
</table>
