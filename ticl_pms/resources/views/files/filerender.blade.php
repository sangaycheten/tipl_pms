<html>
<head>
    <script src="https://documentcloud.adobe.com/view-sdk/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <style>
        #the-canvas {
            border: 2px solid #000;
        }
    </style>
</head>
<body>
{{--
<div>
   <button id="prev">Previous</button>
   <button id="next">Next</button>
   &nbsp; &nbsp;
   <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
</div>
<br/>
<canvas id="the-canvas"></canvas>
--}}
<div id="adobe-dc-view" style="height:600px"></div>
<script>
    //$(document).ready(function(){
        if($("#adobe-dc-view").length){
            var innerWidth = $("#adobe-dc-view").innerWidth();
            if(innerWidth > 900){
                var height = (innerWidth)*1.47;
            }else{
                var height = (innerWidth)*1.58;
            }
            $("#adobe-dc-view").attr('style','height: '+height+'px');
            document.addEventListener("adobe_dc_view_sdk.ready", function(){
                console.log('zz');

                var adobeDCView = new AdobeDC.View({clientId: "a5b0add7c7134fe2836c4d3006ba546f", divId: "adobe-dc-view"});
                adobeDCView.previewFile({
                    content:{location: {url: "{{asset($file)}}"}},
                    metaData:{fileName: "{{$name}}"}
                },/*{embedMode: "IN_LINE"}*/ {defaultViewMode: "FIT_WIDTH", showAnnotationTools: false, showLeftHandPanel: false,
                    showDownloadPDF: false,showPageControls: false, showPrintPDF: false});
            });
        }

    //});
</script>
</body>
</html>
