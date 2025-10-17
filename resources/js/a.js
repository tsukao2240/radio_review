$(function(){
    //tableの行数を取得
    var tableLine = $(".table01 tr").length;
    //各行ごとに処理
    for (var i=0; i<tableLine; i++) {
        //行の高さをtable01・table02でそれぞれ取得
        var tableLineHeight01 = $('.table01 tr').eq(i).innerHeight();
        var tableLineHeight02 = $('.table02 tr').eq(i).innerHeight();
        //高さの数値が大きい方に合わせる
        if(tableLineHeight01 > tableLineHeight02){
            $(".table02 tr").eq(i).css("height",tableLineHeight01 + "px");
        } else if(tableLineHeight02 > tableLineHeight01){
            $(".table01 tr").eq(i).css("height",tableLineHeight02 + "px");
        } else {
            ;
        }
    }
});
