@extends('layouts.main')
@section('style')
<link rel="stylesheet" href="{{asset('/')}}css/frame-detail.css">
<link href="{{asset('/')}}css/basic.css" rel="stylesheet">
<link href="{{asset('/')}}css/custom2.css" rel="stylesheet">
<style>
#image_frame {
	/* border-image-slice:initial!important;*/
}
    .print-type-product ul li {
        max-width: 32%;
        text-align: center;
        cursor: pointer;
    }
    .panel-body {
        padding: 15px;
    }
    .panel-heading {
        padding: 15px;
    }
    .print_radio {
        display: none;
    }
    .canvas-edge img {

        box-shadow:2px 2px 0px #999, 4px 4px 0px #999, 5px 5px 0px #999, 6px 6px 0px #999, 7px 7px 0px #999, 8px 8px 0px #999, 9px 9px 0px #999, 10px 10px 0px #999, 11px 11px 0px #999, 12px 12px 0px #999, 13px 13px 0px #999, 14px 14px 0px #999, 15px 15px 0px #999;
    }
    .acrylic-dots {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0px;
        top: 0px;
    }

    .acrylic-dots span {
        width: 15px;
        height: 15px;
        display: block;
        position: absolute;
        background: url(../images/acry-btn3.png);
        background-size: 15px;
    }

    span.dots-left-top {
        left:20px;
        top: 20px;
    }

    span.dots-right-top {
        right:20px;
        top: 20px;
    }

    span.dots-left-bottom {
        left: 20px;
        bottom: 20px;
    }

    span.dots-right-bottom {
        right: 20px;
        bottom: 20px;
    }
    .print-type-product ul li.active img {
        border: 2px solid #337ab7;
    }
    #customsize{
        display: none;
    }
    #customsizecheck{
        display: inline;
    }

</style>
@endsection
@section('content')
<div class="container">
    <div class="flash-message">  </div>
</div>
<div class="container">
    <ol class="breadcrumb">
        <li class=""> <a href="{{url('/')}}">Home</a></li>
        <li class=""> <a href="{{url('/products-and-services')}}">Products and Services</a></li>
        <li class=""> <a href="#">Frames & Framing</a></li>
    </ol>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Frames &amp; Framing</h1>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="row">
                            <div class="col-md-9">
                                <h2>3 Easy Steps To Design Your Own Photo Frame</h2>
                                <p>1. Choose a Frame from one of 10 Unique Categories.</p>
                                <p>2. Select a Preset Size from the dropdown menu, or select 'Custom Size' to input your own measurements.</p>
                                <p>3. Choose up to 3 Mat layers.(If mat is not required, go straight to shopping cart).</p>
                                <p>Preview your Frame before ordering!</p>
                                <p>Learn more from our Tutorial Video about How to Order Photo Frames. For further help, feel free to call us on (02) 9659 6696.</p>
                                <hr>
                            </div>
                            <div class="col-md-3"><iframe src="https://www.youtube.com/embed/PeRMerdzBKE" width="200" height="150" frameborder="0" allowfullscreen=""></iframe></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    @include('frame.options')
                </div> 
                <div class="col-md-7">
                    <div class="panel panel-default  custom-panel">
                        <div class="panel-heading">Preview</div>
                        <div class="panel-body" style="background-color:#f5f5f5">
<img src="{{asset('/images/step1_nob.jpg')}}" id="stepsimg" style="margin: 0 auto 20px auto;">
                            <div id="framecontainer" class="detail-wrapper-left text-center">
                                <input type="hidden" name="image_id" id="image_id" value="">
                                <div id="image_frame" class="" >
                                    <div id="top-mat" >
                                        <div id="middle-mat" >
                                            <div id="bottom-mat" >
                                                <img style="" id="uploaded_img" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="img-responsive center-block">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right preview_final">

                            </div>
                            <div class="preview_text">
							<small>
                                <p>Please Note: All frames come with 2mm Clear Glass and 5mm foamboard backing(incl). If you would like to change the glass or backing, please select from the drop down features under Your Order below.</p>
<p>All 4x6", 5x7", 6x8" and 8x10" frames come with MDF easel stands.</p>
<p>Frame size over 60 * 90 cm using courier service, we highly recommend using 2mm clear Perplex/acrylic instead of glass to avoid breakage.</p>
							</small>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="col-md-5">
                    <div class="detail-wrapper-right">
                        <div class="print-size-product">
                            <div class="panel panel-default  custom-panel">
                                <div class="panel-heading"> Options </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12">

                                            <label>Do You Want Custom Size ? <input type="checkbox" class="checkbox" id="customsizecheck"></label>

                                            <div class="form-group">
                                                <label>Size<span id="mm" style="display:none;">(mm)</span> </label>

                                                <select name="size" id="product_size" class="form-control options">
                                                    <option value="0">Select Size & Price</option>
                                                    @foreach($sizes as $size)
                                                    <option value="{{$size->id}}" data-width="{{$size->width}}" data-height="{{$size->height}}" id="ffsize-<?=$size->id;?>" data-id="<?=$size->id;?>"><?=$size->title;?>&minus; </option>
                                                    @endforeach
                                                </select>												
                                                <div id="customsize" class="row">
                                                    <div class="col-xs-6">
                                                        <input id="size_width" placeholder="Width" class="form-control options number customWidth" type="text">
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <input id="size_height" placeholder="height" class="form-control options number customHeight" type="text">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="form-group">
                                                <label>Qty</label>

                                                <select name="qty" id="qty" class="form-control options">
                                                    @for($i=1; $i<=100; $i++)
                                                    <option value="{{$i}}" >{{$i}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Glass</label>											
                                                <select name="glass" id="glass" class="form-control options">
                                                    @foreach($glass as $g)
                                                    <option value="{{$g->id}}" >{{$g->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Backing</label>

                                                <select name="backing" id="backing" class="form-control options">
                                                    @foreach($backing as $back)
                                                    <option value="{{$back->id}}" >{{$back->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Comments / Notes</label>
                                                <textarea name="comments" id="comments" class="form-control options"></textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="your_order"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="token" value="{{csrf_token()}}">
@endsection	
@section('script')
@include('frame.js.framesframing')
@endsection
