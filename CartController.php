<?php namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Routing\ResponseFactory;
use Omnipay\Omnipay;
use Omnipay\Common\GatewayFactory;
use Illuminate\Support\Facades\Input;
use Validator;
use Mail;
use File;
use App\State;
use App\Common;
use App\Frames;
use App\Cart;
use App\Category;
use App\MatCat;
use App\Subservice;
use Session;
use Redirect;
use URL;
/** All Paypal Details class * */
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class CartController extends Controller {
     private $_api_context;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //$this->middleware('auth');
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function cartview(Request $req) {

        $user = session('user');
        $session_id = session()->getId();
        if (!empty($user)) {
            $data['cartdata'] = Cart::where('user_id', '=', $user->id)->get();
        } else {
            $data['cartdata'] = Cart::where('session_id', '=', $session_id)->get();
        }
        //dd($data);
        return view('cart', $data);
    }

    public function updatecart(Request $req) {

        $cart_id = $req->get('cart');
        if (!empty($cart_id)) {
            foreach ($cart_id as $k => $v) {
                Cart::where('id', $k)->update(['qty' => $v]);
            }
        }
        return redirect('cart');
    }

    public function deletecartitem(Request $req) {
        $cart_id = $req->get('cart_id');
        Cart::where('id', $cart_id)->delete();
        return redirect('cart');
    }

    public function supplyAddToCart($id, $qty = 1) {

        $session_id = session()->getId();
        $user = session('user');
        $user_id = 0;
        if (!empty($user)) {
            $user_id = $user->id;
        }
        DB::table('cart')->insert([
            'qty' => $qty,
            'Product' => 'Supply',
            'supply' => $id,
            'user_id' => $user_id,
            'session_id' => $session_id
        ]);

        return redirect('cart');
    }


    // ***** WTF is this?
    // - How do we set required fields?
    // - ... more unreadable than Drupal or Wordpress ahhhhhh
    // 
    public function getCartItem(Request $req) {


     // print_r($req->all());
	 // die;
        if($req->get("border_acrylic_only") == 2 && $req->get("medium") == 5){
             
            $all_data = $req->all();
            $all_data['width'] = $all_data['width'] + 100;
            $all_data['height'] = $all_data['height'] + 100;

            $req->merge([
                'width' => $all_data['width'],
                'height' => $all_data['height'],
            ]);



        }elseif ($req->get("border_acrylic_only") == 3 && $req->get("medium") == 5) {
			
			
            $all_data = $req->all();
            $all_data['width'] = $all_data['width'] + 140;
            $all_data['height'] = $all_data['height'] + 140;

            $req->merge([
                'width' => $all_data['width'],
                'height' => $all_data['height'],
            ]);
        }


        $old_width = $req->get("old_width");
        $old_height = $req->get("old_height");
        // var_dump($req);de();
        if($req->get("timber_thickness")){
            $op = $req->get("timber_thickness");
            if($op === "40mm" || $op === "20mm"){

                $data["custom_options"][0]["val"] = $op;
                $data["custom_options"][0]["key"] = "Timber Thickness";
            }
        }
        $poster_print = false;
        if($req->get('product_id')){
            $parent_id = DB::select("SELECT * FROM styles JOIN product ON product.style=styles.id WHERE product.id=?", [$req->get("product_id")])[0]->parent;
            if($parent_id == 3) // Check if parent = 3 (Poster Print product)...
            {
                $poster_print = true;
                $size=DB::select("SELECT * FROM size WHERE product=? LIMIT 1", [$req->get("product_id")])[0];
                $data['size'][0] = $size->width;
                $data['size'][1] = $size->height;
                // $data['total_override'] = $size->price;
                // return view('cartItem', $data); // TODO framing needs to be included here... 
            }
        }
        if ($req->get("block_mount_upload")){
			 if($req->get("type")){
				$a = $this->get_block_mount_upload_price($req->get("type"), $req->get("width"), $req->get("height"));
				$data["hardcoded_product"][] = $a;
                $options["hardcoded_product"][] = $a;
            }
        }
        if ($req->get('comments')) {
            $data['comments'] = $req->get('comments');
        }
        if ($req->get('glass')) {
            $data['glass'] = DB::table('glass')->where('id', $req->get('glass'))->first();
        }
        if ($req->get('backing')) {
            $data['backing'] = DB::table('backing')->where('id', $req->get('backing'))->first();
        }
        $tableArray = get_option_table_array();
        foreach ($tableArray as $option) {

            if ($req->get($option)) {
                $data['options'][$option] = DB::table($option)->where('id', $req->get($option))->first();
            }
        }
        if ($req->get('image') && !$req->get('service')) {
			
            $temp_media = DB::table('temp_media')->where('id', $req->get('image'))->first();
            if ($req->get('qty') < 0) {
                $data['qty'] = 1;
            } else {
                $data['qty'] = $req->get('qty');
            }
            $data['image'] = array('price' => 15, 'name' => $temp_media->name);
            $destinationPath = public_path('/uploads');
            //$size = getimagesize($destinationPath.'/'.$temp_media->name);
            $data['size'] = array($req->get('width'), $req->get('height'));

            if ($req->get('medium')) {
                $data['medium'] = DB::table('medium')->where('id', $req->get('medium'))->first();
            }

            if ($req->get('frame')) {
                $data['frame'] = DB::table('frames')->where('frame_id', $req->get('frame'))->first();
            }
            $matwidth = 0;
            if ($req->get('mats')) {
                $matsdata = [];
                foreach ($req->get('mats') as $mat) {
                    $matwidth += $mat['matwidth'];
                    $matinfo = DB::table('mats')->where('id', $mat['matid'])->first();
                    $asd = ['title' => $matinfo->title, 'cat' => $matinfo->cat, 'price' => $matinfo->price, 'width' => $mat['matwidth'], 'id' => $mat['matid']];
                    if (isset($mat['maton'])) {
                        $asd['maton'] = $mat['maton'];
                    }
                    $matsdata[] = $asd;
                }
                $data['matsdata'] = $matsdata;
            }
            $data['old_width'] = $old_width;
            $data['old_height'] = $old_height;
            // $data['medium'] = $medium;
            return view('cartItem', $data);
        } elseif ($req->get('product_id') && $req->get('size')) {
			
            if ($req->get('medium')) {
                $data['medium'] = DB::table('medium')->where('id', $req->get('medium'))->first();
                // var_dump($data['medium']);
                // if(!$poster_print || $req->get('medium') == 3) // poster print doesn't have mediums unless framing (= 3)..... magic number city. TODO throw all these magic numbers in a config file somewhere.
                // {
                //     // $data['medium']['price'] = 0;
                // }
            }
            /* $data['product_price'] = DB::table('product_size_mapping')->where([['product_id',$req->get('product_id')],['size_id',$req->get('size')]])->value('price'); */

            $data['product'] = DB::table('product')->where('id', $req->get('product_id'))->first();
            $data['product_size'] = DB::table('size')->where('id', $req->get('size'))->first();
            if ($req->get('qty') < 0) {
                $data['qty'] = 1;
            } else {
                $data['qty'] = $req->get('qty');
            }
            if ($req->get('frame')) {
                $data['frame'] = DB::table('frames')->where('frame_id', $req->get('frame'))->first();
            }
            if ($req->get('mats')) {
                $matsdata = [];

                foreach ($req->get('mats') as $mat) {
                    $matinfo = DB::table('mats')->where('id', $mat['matid'])->first();
                    $asd = ['title' => $matinfo->title, 'cat' => $matinfo->cat, 'price' => $matinfo->price, 'width' => $mat['matwidth'], 'id' => $mat['matid']];
                    if (isset($mat['maton'])) {
                        $asd['maton'] = $mat['maton'];
                    }
                    $matsdata[] = $asd;
                }
                $data['matsdata'] = $matsdata;
            }
            $data['old_width'] = $old_width;
            $data['old_height'] = $old_height;
            return view('cartItem', $data);
        } elseif ($req->get('service')) {
			
			
            $data['service'] = DB::table('services')->where('id', $req->get('service'))->first();
            if ($req->get('subservice')) {
                $data['subservice'] = DB::table('subservices')->where('id', $req->get('subservice'))->first();
            }
            if ($req->get('mirror_type')) {
                if ($req->get('size_height') && $req->get('size_width')) {
                    (int) $size_height = $req->get('size_height');
                    (int) $size_width = $req->get('size_width');
                    if ($size_width > 0 && $size_height > 0) {
                        $data['mirror_type'] = DB::table('mirror_type')->where('id', $req->get('mirror_type'))->first();
                        $data['custom_size'] = [$size_width, $size_height];
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
            }
            if ($req->get('qty') < 0) {
                $data['qty'] = 1;
            } else {
                $data['qty'] = $req->get('qty');
            }
            $data['upload_option'] = $req->get('upload_option');
            if ($req->get('image') && $req->get('upload_option') == 1) {
				
                $temp_media = DB::table('temp_media')->where('id', $req->get('image'))->first();
                $data['image'] = array('price' => 0, 'name' => $temp_media->name);
				if($req->get('edge_colour')){
				$data['edge_color'] = $req->get('edge_colour');
				}
				if($req->get('edge_colour_delux')){
				$data['edge_color_delux'] = $req->get('edge_colour_delux');
				}
				$data['edge_laminate'] = $req->get('edge_liminate');
				$data['edge_hanger'] = $req->get('edge_hanger');
				$data['block_width'] =  $req->get('width');
				$data['block_height'] =  $req->get('height');
				
            }
            if ($req->get('size')) {
                $size = $req->get('size');
                if (is_array($size)) {
                    $data['custom_size'] = $size;
                } else {
                    $data['size'] = DB::table('size')->where('id', $req->get('size'))->first();
                }
            }
            
            if ($req->get('frame')) {
                $data['frame'] = DB::table('frames')->where('frame_id', $req->get('frame'))->first();
            }
            if ($req->get('mats')) {
                $matsdata = [];
                foreach ($req->get('mats') as $mat) {
                    $matinfo = DB::table('mats')->where('id', $mat['matid'])->first();
                    $matsinfo = ['title' => $matinfo->title, 'price' => $matinfo->price, 'cat' => $matinfo->cat];
                    if (isset($mat['matwidth'])) {
                        $matsinfo['width'] = $mat['matwidth'];
                    }

                    if (isset($mat['maton'])) {
                        $matsinfo['maton'] = $mat['maton'];
                    }


                    $matsdata[] = $matsinfo;
                }
                $data['matsdata'] = $matsdata;
            }

            if ($req->get('matsproduct')) {
                $data['matsproduct'] = DB::table('mat_product')->where('id', $req->get('matsproduct'))->first();
            }

           
          
            if ($req->get('mat')) {
                $maxlength = 0;
                if ($req->get('line1')) {
                   $data['line1'] = $req->get('line1');
                   $maxlength = strlen($data['line1']);
                   $data['namematinfo1'] = DB::table('namematprice')->where('line', 1)->where('latters', $maxlength)->first();
				   
				   
                   // var_dump($data['namematinfo']);
                }
                if ($req->get('line2')) {
                    $data['line2'] = $req->get('line2');
                    if(strlen($data['line2'])>$maxlength){
                        $maxlength = strlen($data['line2']);
						
                    }
					$data['namematinfo1']->price = 0;
                    $data['namematinfo2'] = DB::table('namematprice')->where('line', 2)->where('latters', $maxlength)->first();
	
                }
                $data['namemat'] = DB::table('mats')->where('id', $req->get('mat'))->first();
				
            }
			 
            
            return view('serviceItem', $data);
        }
    }



    public function addtocarts(Request $req) {



         if($req->get("border_acrylic_only") == 2 && $req->get("medium") == 5){

            $all_data = $req->all();
            $all_data['width'] = $all_data['width'] + 100;
            $all_data['height'] = $all_data['height'] + 100;

            $req->merge([
                'width' => $all_data['width'],
                'height' => $all_data['height'],
            ]);



        }elseif ($req->get("border_acrylic_only") == 3 && $req->get("medium") == 5) {
            $all_data = $req->all();
            $all_data['width'] = $all_data['width'] + 140;
            $all_data['height'] = $all_data['height'] + 140;

            $req->merge([
                'width' => $all_data['width'],
                'height' => $all_data['height'],
            ]);
        }
        
        $options = [];
        if ($req->get('glass')) {
            $options['glass'] = $req->get('glass');
        }



        $cart = new Cart;
        $cart->session_id = session()->getId();
        if($req->get("timber_thickness")){
            $op = $req->get("timber_thickness");
            if($op === "40mm" || $op === "20mm"){

                $options["custom_options"][0]["val"] = $op;
                $options["custom_options"][0]["key"] = "Timber Thickness";
            }
        }
        $user = session('user');
        if (!empty($user)) {
            $cart->user_id = $user->id;
        }
        if ($req->get('qty') < 0) {
            $cart->qty = 1;
        } else {
            $cart->qty = $req->get('qty');
        }
        if ($req->get('comments')) {
            $options['comments'] = $req->get('comments');
        }
        if ($req->get('medium')) {
            $options['medium'] = $req->get('medium');
        }

        if ($req->get('mats')) {
            foreach ($req->get('mats') as $mat) {
                $md = ['id' => $mat['matid']];
                if (isset($mat['matwidth'])) {
                    $md['width'] = $mat['matwidth'];
                }
                if (isset($mat['matwidth'])) {
                    $md['maton'] = $mat['maton'];
                }
                $options['mats'][] = $md;
            }
        }
        if ($req->get('image') && !$req->get('service')) {
            if ($req->get('width') == "" || $req->get('height') == ""){
                return response()->json(['status' => false, 'msg' => 'You need to enter a width and height.']);
            }
            // TODO required fields:
            $medium_id = $req->get('medium');
            if($medium_id == 3){ // photo printing and framing
                if($req->get('glass') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a glass type.']);
                }
                if($req->get('frame') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a frame.']);   
                }
                // return response()->json(['status' => false, 'msg' => 'size is compulsory.']);
            }else if ($medium_id == 1){ // just print 
            }else if ($medium_id == 2){ // just canvas
            }else if ($medium_id == 4){ // canvas stretching
                // if ($req->get('effect') == 0){
                //     return response()->json(['status' => false, 'msg' => 'Please select an effect.']);
                // }
                if ($req->get('timbersize') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a timber size.']);
                }
                if ($req->get('edge_type') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select an edge type.']);
                }
            }else if ($medium_id == 5){ // acrylic mount
                // if($req->get('effect') == 0){
                //     return response()->json(['status' => false, 'msg' => 'Please select an effect.']);
                // }
                if($req->get('backing_color') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a backing colour.']);
                }
                if($req->get('hanging_system') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a hanging system.']);
                }
                // if($req->get('border') == 0){
                //     return response()->json(['status' => false, 'msg' => 'Please select a border.']);
                // }
            }else if ($medium_id == 6){ // block mount
                if($req->get('edge_type') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select an edge type.']);
                }
                if($req->get('finishtype') == 0){
                    return response()->json(['status' => false, 'msg' => 'Please select a laminate.']);
                }
            }
            $cart->product = 'Photo Printing and Framing';

            $options['size'] = [$req->get('width'), $req->get('height')];
            $options['images'] = array($req->get('image'));
            if ($req->get('frame')) {
                $options['frame'] = array($req->get('frame'));
            }

            $tableArray = get_option_table_array();
            foreach ($tableArray as $option) {
                if ($req->get($option)) {
                    $options[$option] = $req->get($option);
                }
            }
            if ($req->get('glass')) {
                $options['glass'] = $req->get('glass');
            }
        } elseif ($req->get('product_id')) {

            $parent_id = DB::select("SELECT * FROM styles JOIN product ON product.style=styles.id WHERE product.id=?", [$req->get("product_id")])[0]->parent;
            if($parent_id == 3) // Check if parent = 3 (Poster Print product)...
            {
                $options['medium'] = null;
                // $size=DB::select("SELECT * FROM size WHERE product=? LIMIT 1", [$req->get("product_id")])[0];
                // $data['size'][0] = $size->width;
                // $data['size'][1] = $size->height;
                // $data['total_override'] = $size->price;
                // return view('cartItem', $data);
            }

            $options['product'] = $req->get('product_id');

            $productdata = DB::table('styles')->select('styles.parent')->join('product', 'product.style', '=', 'styles.id')->where('product.id', $req->get('product_id'))->first(15);
            if ($productdata->parent == 1)
                $cart->product = "Buy art Online";
            else
                $cart->product = "Poster Prints";
            if ($req->get('frame')) {
                $options['frame'] = array($req->get('frame'));
            }
            if ($req->get('size')) {
                $options['size'] = $req->get('size');
            }


            $tableArray = get_option_table_array();
            foreach ($tableArray as $option) {
                if ($req->get($option)) {
                    $options[$option] = $req->get($option);
                }
            }
        } elseif ($req->get('service')) {

            $service = DB::table('services')->where('id', $req->get('service'))->first();
            $cart->product = $service->service;
            if ($req->get('subservice')) {
                $subservice = DB::table('subservices')->where('id', $req->get('subservice'))->first();
                $cart->product = $subservice->title;
                $options['subservice'] = $req->get('subservice');
            }
            $options['services'] = $req->get('service');
            if ($req->get('size')) {
                $options['size'] = $req->get('size');
            }
            if ($req->get('mirror_type')) {
                if ($req->get('size_height') && $req->get('size_width')) {
                    (int) $size_height = $req->get('size_height');
                    (int) $size_width = $req->get('size_width');
                    if ($size_width > 0 && $size_height > 0) {
                        $options['mirror_type'] = $req->get('mirror_type');
                        $options['custom_size'] = [$size_width, $size_height];
                    } else {
                        return response()->json(['status' => false, 'msg' => 'size should integer.']);
                    }
                } else {
                    return response()->json(['status' => false, 'msg' => 'size is compulsory.']);
                }
            }

            $options['upload_option'] = $req->get('upload_option');
            if ($req->get('image') && $req->get('upload_option') == 1) {
                $options['images'] = is_array($req->get('image')) ? $req->get('image') : array($req->get('image'));
            }
            if ($req->get('frame')) {
                $options['frame'] = array($req->get('frame'));
            }

            if ($req->get('matsproduct')) {
                $options['matsproduct'] = $req->get('matsproduct');
            }
            if ($req->get('glass')) {
                $options['glass'] = $req->get('glass');
            }
            if ($req->get('backing')) {
                $options['backing'] = $req->get('backing');
            }
			
            if ($req->get('edge_liminate')) {
                $options['edge_liminate'] = $req->get('edge_liminate');
            }
			
		
				if ($req->get('edge_hanger')) {
					$options['edge_hanger'] = $req->get('edge_hanger');
				}
			
			
				if ($req->get('edge_colour_delux')) {
					$options['edge_colour_delux'] = $req->get('edge_colour_delux');
				}
			
			
				if ($req->get('edge_colour')) {
					$options['edge_colour'] = $req->get('edge_colour');
				}
				if ($req->get('type')) {
					$options['type'] = $req->get('type');
				}
				if ($req->get('width')) {
					$options['width'] = $req->get('width');
				}
				if ($req->get('height')) {
					$options['height'] = $req->get('height');
				}
			
            $tableArray = get_option_table_array();
            foreach ($tableArray as $option) {
                if ($req->get($option)) {
                    $options[$option] = $req->get($option);
                }
            }
            if ($req->get('mat')) {
                $maxlength = 0;
                $options['namemat']=[];
                if ($req->get('line1')) {
                   $options['namemat']['line1']=$req->get('line1');
                   
                }
                if ($req->get('line2')) {
                   $options['namemat']['line2']=$req->get('line2');
                }
                
                $options['namemat']['mat']=$req->get('mat');
            }
        } else {
            return response()->json(['status' => false, 'msg' => 'please upload image'], 404);
        }
        if ($req->get("block_mount_upload")){
			
            if($req->get("type")){
                $a = $this->get_block_mount_upload_price($req->get("type"), $req->get("width"), $req->get("height"));
                $data["hardcoded_product"][] = $a;
                $options["hardcoded_product"][] = $a;
            }
        }

        if (isset($options['frame'])) {
            if ($req->get('frame')) {
                $options['frame'] = array($req->get('frame'));
            }
        }
        if (isset($options['size'])) {
            if ($req->get('size')) {
                $options['size'] = $req->get('size');
            }
        }
		if (isset($options['edge_liminate'])) {
            if ($req->get('edge_liminate')) {
                $options['edge_liminate'] = $req->get('edge_liminate');
            }
        }
		if (isset($options['edge_hanger'])) {
            if ($req->get('edge_hanger')) {
                $options['edge_hanger'] = $req->get('edge_hanger');
            }
        }
		if (isset($options['edge_colour_delux'])) {
            if ($req->get('edge_colour_delux')) {
                $options['edge_colour_delux'] = $req->get('edge_colour_delux');
            }
        }
		if (isset($options['edge_colour'])) {
            if ($req->get('edge_colour')) {
                $options['edge_colour'] = $req->get('edge_colour');
            }
        }
		

        $cart->options = serialize($options);
        $cart->save();
        return response()->json(['status' => true], 200);
    }



private function get_block_mount_upload_price($type, $width, $height) {
    $a = [];

    $a["title"] = "";

    $a["price"] = "";

    if ($type === "basic") {
        $rlp = 0.5;
        $title = "5mm Block Mount";
    } else if ($type === "premium") {
        $rlp = 0.7;
        $title = "10mm Block Mount";
    } else if ($type === "deluxe") {
        $title = "20mm Block Mount";
        $rlp = 1.2;
    }

    $a["price"] = (($width + $height) * $rlp)/10;

    $a["title"] = $title;

    return $a;
}

}