<div class="photo-frame-style">
    <div class="panel panel-default  custom-panel">
        <div class="panel-heading">
            Choose a Frame <span id="frametitle"></span>
        </div>
        <div class="panel-body">
            <p id="msg" class="text-danger"></p>
            <div class="row" id="frame-options">
                <?php if(isset($mats)):?>
                <div class="col-xs-3 col-sm-1 p-r-0">
                    <ul class="nav nav-tabs frames-tab" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#frameLi" id="frametab" aria-controls="frameLi" role="tab" data-toggle="tab">Frames</a>
                        </li>
                        <li role="presentation">
                            <a href="#matLi" id="matstab" aria-controls="matLi" role="tab" data-toggle="tab">Mats</a>
                        </li>
                    </ul>
                <?php endif;?>
                </div>
                <div class="<?php if(isset($mats)):?>col-xs-9 col-sm-11 p-l-0<?php else:?>col-md-12<?php endif;?>">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="frameLi">
                            <ul class="nav nav-tabs tabs-scroll" role="tablist">
                                @foreach($categories as $i=>$cate)
                                <li role="presentation" class="{{$i==0 ? 'active' : ''}}">
                                    <a href="#frame_{{$cate->id}}" aria-controls="home" role="tab" data-toggle="tab"><?php if($cate->name == ""):?>{{$cate->title}}<?php else:?>{{$cate->name}}<?php endif;?> </a>
                                </li>
                                @endforeach
                            </ul>
                            <div class="tab-content scroll">
                                @foreach($categories as $i=>$cate)
                                <div role="tabpanel" class="tab-pane {{$i==0 ? 'active' : ''}}" id="frame_{{$cate->id}}">
                                    <div class="frame-lists">
                                        @foreach($cate->frames as $j=>$frame)
                                        <div class="frames">
                                            <img data-id="{{$frame->frame_id}}" data-thickness="{{$frame->thickness}}" data-img="url('{{asset('/images/frames/'.$frame->img)}}') 30 stretch"  src="{{asset('/images/frames/'.$frame->thumb)}}" data-width="{{$frame->border}}">
                                            <div class="frame-details">
                                                <p><b>Code: </b><span id="frame_name92">{{$frame->code}}</span></p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                                <input type="hidden" name="frame_id" id="frame_id" value="">
                            </div>
                        </div>
                        <?php if(isset($mats)):?>
                        <div role="tabpanel" class="tab-pane" id="matLi">
                            <div class="col-md-5 col-sm-12 mat-layers">
                                <span> Select Number of Mats </span>
                                <div class="selection">
                                    <?php if(! isset($mattsname) && ! isset($windowmatt) && ! isset($mattssingle)):?>
                                        <input name="mat_type" value="0" class="mat_num_cls " checked="checked" type="radio" onclick="select_mat(0)"> none
                                    <?php endif;?>
                                    <input name="mat_type" value="1" class="mat_num_cls" type="radio" onclick="select_mat(1)"> 1
                                    <?php if(! isset($mattsname)):?>
                                        <input name="mat_type" value="2" class="mat_num_cls" type="radio" onclick="select_mat(2)"> 2
                                        <?php if(! isset($windowmatt) && ! isset($mattssingle)):?>
                                            <input name="mat_type" value="3" class="mat_num_cls" type="radio" onclick="select_mat(3)"> 3
                                        <?php endif;?>
                                    <?php endif;?>
                                </div>
                                <?php $mate_type = ['Top Mat', 'Middle Mat', 'Bottom Mat'];

                                 
								?>
								
								
                                @foreach($mats as $i=>$mat)
								
								
								
                                @if($i > 2)
                                <?php break; ?>
                                @endif
                                <div class="mat-size {{($i==0) ? 'selected' : ''}} mat_info" id="matType{{$i+1}}" style="display: none;">
                                    <img src="{{asset('/images/matt/'.$mat->img)}}">
                                    <div class="matt-name">
                                        <p>{{isset($mate_type[$i]) ? $mate_type[$i] : ''}}</p>
                                        <p class="mat_name">{{$mat->title}}</p>
                                    </div>
									 
                                    <div class="matt-size ">
									 
                                        <input type="hidden" class="mat_id" name="mat_id_{{$i+1}}" id="mat_id_{{$i+1}}" value="{{$mat->id}}" />
									<?php if(!isset($mattssingle) && (! isset($windowmatt))){?>
                                        mm<input class="mat_width" id="mat_width_{{$i+1}}" name="mat_width_{{$i+1}}" value="{{$i==0 ? 50 : 5}}" type="number" min="10" max="50">
										<?php }else{ ?>
									     <input class="mat_width" id="mat_width_{{$i+1}}" name="mat_width_{{$i+1}}" value="{{$i==0 ? 50 : 5}}" type="hidden" min="10" max="50">
										<?php }?>
                                    </div>
									
                                </div>
                                @endforeach
                                <div class="mat-notice">
                                    <p style="font-size:12px;">By default the top mat width is 50mm and the second and third Mat widths are 5mm. You can change the width in the box.<br>To change colour, please click on the colour in the box on the right</p>
                                </div>
                            </div>
                            <div class="col-md-7 col-sm-12 ">
                                <div class="select-matt" id="mat_type_name">
                                    <span>Select Mat</span>
                                </div>
                                <div class="mat-listing">
                                    <div class="mat-column">
                                        @foreach($mats as $i=>$mat)
                                        <div class="mats">
                                            <img width="100%" data-id="{{$mat->id}}" data-title="{{$mat->title}}" src="{{asset('/images/matt/'.$mat->img)}}">
                                                            </div>
                                            @if(($i+1)%4==0)
                                        </div>
                                        <div class="mat-column">
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
