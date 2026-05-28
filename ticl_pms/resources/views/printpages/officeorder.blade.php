<html>
<head>
    <style>
        table {
            border-collapse: collapse; border: 1px solid black;
        }
        table, table tr td, table tr th {
            page-break-inside: avoid;
        }
        @media print {
            table, table tr td, table tr th {
                page-break-inside: avoid;
            }
        }
        th, td {
            border: 1px solid black;
        }
        body{
            padding-top:100px!important;
            padding-left:20px;
            padding-right:20px;
            line-height:23px!important;
            font-size:15.5px;
        }
        header {
            position: fixed;
            left: 0px;
            right: 0px;
            height:100px;
        }
        footer {
            position: fixed;
            margin-left: 4.5%;
            width:85%;
            bottom:24px;
            padding-top:15px;
            line-height:18px!important;
        }
        @page {
            margin: 25px 25px 50px 25px;
            padding-bottom:20px;
            padding-top:30px;
        }
        [data-f-id="pbf"]{
            display:none!important;
	}
    </style>
</head>

<?php 
    $opciones_ssl=array(
            "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    
    $img_path = 'images/MDSig.png';
    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
    $data = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
    $img_base_64 = base64_encode($data);
    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;

    $img_pathBG = 'images/tashicell_letterhead.png';
    $extencionBG = pathinfo($img_pathBG, PATHINFO_EXTENSION);
    $dataBG = file_get_contents($img_pathBG, false, stream_context_create($opciones_ssl));
    $img_base_64BG = base64_encode($dataBG);
    $path_imgBG = 'data:image/' . $extencion . ';base64,' . $img_base_64BG;

    $img_pathFT = 'images/tashicell_letterfooter.png';
    $extension = pathinfo($img_pathFT, PATHINFO_EXTENSION);
    $dataFT = file_get_contents($img_pathFT, false, stream_context_create($opciones_ssl));
    $img_base_64FT = base64_encode($dataFT);
    $path_imgFT = 'data:image/' . $extencion . ';base64,' . $img_base_64FT;
?>

<body style="background-image: url('{{ $path_imgBG }}'); background-repeat: no-repeat;background-position: 51% 0%; ">
     <main style="padding-left:13px;padding-right:13px;padding-top:10px;">
        <div>
            <table style="width:100%;border:none;">
                <tr>
                    <td style="width:50%;border:none;"><strong>Ref No.: </strong>{{$referenceNo}}</td>
                    <td style="width:20%;margin-left:30%;text-align:right;border:none;"><strong>Date: </strong>{!! $date !!}</td>
                </tr>
            </table>
            @if(!$notOrder)
                <h3><center><strong><u>OFFICE ORDER</u></strong></center></h3>
            @endif
            {!! $content !!}
	</div>

        <table style="border:none;">
            <tr>
                <td colspan="2" style="border:none;">
                    <br>
                    <img src="{{$path_img}}" width="110"/>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border:none;">
                    <strong>(Mr. Tashi Tshering)</strong><br/>
                    <strong>Managing Director</strong><br/>
                    <strong>Tashi InfoComm Private Limited</strong><br><br/>
                </td>
            </tr>
	</table>

        <table style="border:none;">
            <tr>
                <td colspan="2" style="border:none;">
                Cc: <br>
                        {!! $cc !!}
                </td>
            </tr>
        </table>
    </main>

    <footer style="min-height: 300px !important; margin: 0 auto; padding: 0 20px;">
        {{-- <table style="bottom: 15px; width: 100%; border-top: 2px solid #000; border-left: 0; border-right: none; border-bottom: none;">
            <tr>
                <td colspan="2" style="border:none;text-align:center;color:#C04424;font-weight:bold;">Address: P.O Box # 1502, Samten Lam, Thimphu: Bhutan</td>
            </tr>
            <tr>
                <td colspan="2" style="border:none;text-align:center;">Phone: +975 77889977 Website: <a href="https://www.tashicell.com">www.tashicell.com</a></td>
            </tr>
        </table> --}}

        <div style="position: fixed; bottom: 0; left: 20%; transform: translate(-20%); width: 100%; border-top: 2px solid #000; border-left: 0; border-right: none; border-bottom: none; display: flex; justify-content: center; padding: 20px;">
            <img src="{{ $path_imgFT }}" />
        </div>
    </footer>

    {{--<div class="content-wrapper"> {!! $content !!}--}}
        {{--{!! $content !!}--}}
        {{--{!! $content !!}--}}
        {{--<img src="{{asset('images/MDSig.png')}}" width="120"/>--}}
        {{--<p>--}}
            {{--<strong>(Mr. Tashi Tshering)</strong><br/>--}}
            {{--<strong>Managing Director</strong><br/>--}}
            {{--<strong>Tashi InfoComm Limited</strong>--}}
        {{--</p>--}}
        {{--Cc: <br>--}}
        {{--<ol>--}}
            {{--{!! $cc !!}--}}
        {{--</ol>--}}
    {{--</div>--}}

    {{--<table width="567">--}}
        {{--<tbody>--}}
        {{--<tr>--}}
            {{--<td width="174" style="text-align:center;">--}}
                {{--<p><strong>Grade &amp; Pay Scale</strong><br><strong>(Old)</strong></p>--}}
            {{--</td>--}}
            {{--<td width="174" style="text-align:center;">--}}
                {{--<p><strong>Grade &amp; Pay Scale</strong><br/><strong>(New)</strong></p>--}}
            {{--</td>--}}
            {{--<td width="108" style="text-align:center;">--}}
                {{--<p><strong>Basic Salary</strong><br/><strong>(Old)</strong></p>--}}
            {{--</td>--}}
            {{--<td width="110" style="text-align:center;">--}}
                {{--<p><strong>Basic Salary</strong><br/><strong>(New)</strong></p>--}}
            {{--</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td width="174">--}}
                {{--<p>T2 Step 5<br/>14,702 &ndash; 374 &ndash; 18,442</p>--}}
            {{--</td>--}}
            {{--<td width="174">--}}
                {{--<p>T2 Step 4<br/>16,176 &ndash; 402 &ndash; 20,191</p>--}}
            {{--</td>--}}
            {{--<td width="108">--}}
                {{--<p>16, 198</p>--}}
            {{--</td>--}}
            {{--<td width="110">--}}
                {{--<p>16,980</p>--}}
            {{--</td>--}}
        {{--</tr>--}}
        {{--</tbody>--}}
    {{--</table>--}}
    <script>
        window.print();
    </script>
    </body>
</html>
