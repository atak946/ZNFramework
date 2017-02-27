<?php

class InternalMForm
{
	protected $_class = [];

	protected $enctypes =
    [
        'multipart'     => 'multipart/form-data',
        'application'   => 'application/x-www-form-urlencoded',
        'text'          => 'text/plain'
    ];

    protected $styles   =
    [
        "Date"          => "",
        "Select2"       => "",
        "Autocomplete"  => "",
        "Mask"          => "",
        "Validation"    => "",
        "Icon"          => ""
    ];

    protected $styleState =
    [
        "Date"          => false,
        "Select2"       => false,
        "Autocomplete"  => false,
        "Mask"          => false,
        "Validation"    => false,
        "Icon"          => false
    ];

    protected $scripts   =
    [
        "Date"          => "",
        "Select2"       => "",
        "Autocomplete"  => "",
        "Mask"          => "",
        "Validation"    => "",
        "Icon"          => ""
    ];

    protected $scriptState =
    [
        "Date"          => false,
        "Select2"       => false,
        "Autocomplete"  => false,
        "Mask"          => false,
        "Validation"    => false,
        "Icon"          => false
    ];

    use MFormTrait;

    public function load(Array $_arr = [], String $type = "style")
    {

    }

	public function open(String $name = NULL, String $FType = "horizontal-form", Array $attr = []) : String
	{
        if($name == "portlet")
        {
            $title = $this->getattr("title", $attr);

            return $this->portlet($name, $title, $attr);
        }

				$attr["name"] = $name;

        $material  = $this->getattr("material", $attr);

        $this->formType = $material == true ? $this->materialclass : "";

		// 1. multipart     => multipart/form-data
        // 2. application   => application/x-www-form-urlencoded
        // 3. text          => text/plain

        if(isset($attr['enctype']))
        {
            $enctype = $attr['enctype'];

            if(isset($this->enctypes[$enctype]))
            {
                $attr['enctype'] = $this->enctypes[$enctype];
            }
        }

        if(!isset($attr["method"]))
        {
        	$attr["method"] = "post";
        }

        if(!isset($attr["class"]))
            $attr["class"] = $FType;
        else
            $attr["class"] = " ".$FType;

        $id = $this->getattr("id", $attr);

        if(empty($id)){
            $id = $name;
        }

        $attr["id"] = $id;
        $this->form_id = $id;

        $validate = $this->getattr("validate", []);

        if(!empty($validate) && $validate != false)
            $this->_validate = true;
        else
            $this->_validate = false;

        $validate_ajax = $this->getattr("ajax", $attr);

        if(!empty($validate_ajax)) $this->validate_ajax = $validate_ajax;

        $return = "<form".$this->attributes($attr)." role='form'> <!-- BEGIN FORM BODY --> <div class='form-body'>".EOL;

        return $return;
	}

	public function close(String $_elem = "form") : String
	{
        $return = "";

        $count = $this->getattr("count", []);
        $validate = $this->_validate;

        if($_elem == "form")
        {
            $return = "</div>
            <!-- END FORM BODY -->
            </form>";
        }
        else if($_elem == "portlet")
        {
            $return = "</div></div></div></div>";
        }
        else
        {
            if(!empty($count))
                for ($i=0; $i < $count; $i++)
                    $return .= "</".$_elem.">";
            else
                $return = "</".$_elem.">";
        }

        if(!empty($validate) && $validate == true && !empty($this->form_id))
        {
            $url = $this->getValidateAttr("url");
            $datatype = $this->getValidateAttr("datatype");
            $method = $this->getValidateAttr("method");
            $callback = $this->getValidateAttr("callback");
            $ajaxreload = $this->getValidateAttr("ajaxreload");
            $callbackurl = $this->getValidateAttr("callbackurl");

            $callbackhtml = "";
            $callbackhtmlerr = "";

            if($callback == "1")
            {
                $callbackhtml = '$("<div class="alert alert-success"><strong>İşlem Başarılı!</strong><div class="callback_content">"+resp+"</div></div>").insertAfter(".page-title");';
                $callbackhtmlerr = '$("<div class="alert alert-danger"><strong>Hata oluştu!</strong><div class="callback_content">"+resp+"</div></div>").insertAfter(".page-title");';
            }
            else if($callback == "2")
            {
                $callbackhtml = "$('.page-content').html(resp);";
                $callbackhtmlerr = "$('.page-content').html(resp);";
            }
            else if($callback == "4"){
                $callbackhtml = "

                    App.blockUI();

                    $.post('{$callbackurl}', function(response){

                        var modal = $('#globalModal', document);

                        modal.find('.modal-content').html(response);

                        modal.modal('show');
                        $('.select2').select2({width:'100%'});

                    });

                ";
            }
            else
            {
                $callbackhtml = "swal({title:'İşlem Başarılı', text:resp2, type:'success', html:true}, function(){

                    var refresh = $('#globalModal').attr('refresh');

                    if(refresh == 'true' || refresh == true)
                    {
                        window.location.reload();
                    }

                });";

                $callbackhtmlerr = "swal({title:'Hata oluştu', text:resp2, type:'error', html:true}, function(){

                    var refresh = $('#globalModal').attr('refresh');

                    if(refresh == 'true' || refresh == true)
                    {
                        window.location.reload();
                    }

                });";
            }

            $return.="
            <script>
                $().ready(function(){
                    $('#".$this->form_id."').validate({
                        errorElement: 'span',
                        errorClass: 'help-block',
                        focusInvalid: false,

                        highlight: function(element) {
                            $(element).closest('.form-group').addClass('has-error');
                        },

                        success: function(label) {
                            label.closest('.form-group').removeClass('has-error');
                            label.remove();
                        },

                        errorPlacement: function(error, element) {
                            $(element).closest('.form-group').append(error);
                        },

                        submitHandler: function(form) {

                            App.blockUI('body');

                            var close = $('#globalModal').attr('close');

                            if(close == 'true' || close == true)
                            {
                                $('#globalModal').modal('hide');
                            }

                            var json = $('#".$this->form_id."').serialize();

                            $.ajax({
                                url: '".$url."',
                                dataType:'".$datatype."',
                                type:'".$method."',
                                data:json,
                                success:function(resp){
                                    if(resp.hata)
                                    {
                                        var resp2 = resp.hata;
                                        ".$callbackhtmlerr."
                                    }
                                    else
									{
										var resp2 = resp.text;
                                        ".$callbackhtml."
									}
                                },
                                error:function(xhr, resp)
                                {

                                },
                                complete:function(){

                                    var ajaxreload = $('#globalModal').attr('ajaxreload');

                                    if(ajaxreload == 'true' || ajaxreload == true)
                                    {
                                        $('.dataTable').DataTable().ajax.reload();
                                    }

                                    App.unblockUI();

                                    var close = $('#globalModal').attr('close');

                                    if(close == 'true' || close == true)
                                    {
                                        $('#globalModal').modal('hide');
                                    }

                                    var modal = $('#globalModal', document);

                                    modal.attr('close', '');
                                    modal.attr('refresh', '');
                                    modal.attr('ajaxreload', '');
                                }
                            });
                        }
                    });
                });
            </script>
            ";
        }

		return $return;
	}

    public function html(String $element = "div", $val = "", Array $_attributes = []) : String
    {
        return "<$element ".$this->attributes($_attributes).">".$val;
    }

    public function portletTitle(String $title = "", Array $_attributes = [])
    {
        $return =
        '
            <div class="portlet-title" '.$this->attributes($_attributes).'>
                <div class="caption font-green-sharp">
                    <i class="icon-settings font-green-sharp"></i>
                    <span class="caption-subject bold uppercase">'.$title.'</span>
                </div>
            </div>

            <br />
        ';

        return $return;
    }

    public function table(String $ID = NULL, Array $cols = [], Array $_attributes = [])
    {
        if(empty($ID)) return "<span style='color:green; font-weight:bold'>MForm::table(</span><span style='color:red; font-weight:bolder;'>'ID'</span>, <span style='color:blue; font-weight:bold;'>['COLUMN' => ['ICON' => 'fa fa-user', 'SHOW'=>'tablet']]', '[Array Attributes]'</span>)";

        $ajaxurl = $this->getattr("ajaxurl", $_attributes);
        $dom = $this->getattr("dom", $_attributes);

        if(!empty($dom)) $dom = ",'dom' : '$dom',";

        $return =
        '
            <table class="table table-striped table-bordered dt-responsive" id="'.$ID.'" '.$this->attributes($_attributes).'>
                    <thead>
                        <tr style="background:#364150; color:white;">
        ';

        $colDefs = "'aoColumnDefs': [";

        $target = 0;
        foreach ($cols as $key => $value) {
            $style = isset($value["STYLE"]) ? $value["STYLE"] : "";
            $show = isset($value["SHOW"]) ? $value["SHOW"] : "";
            $icon = isset($value["ICON"]) ? $value["ICON"] : "";
            $wd = isset($value["WIDTH"]) ? $value["WIDTH"] : "";

            if(!empty($wd))
            {
                $colDefs.="{'width':'$wd', 'targets': $target},";
            }

            $return.='<th style="'.$style.'" class="'.$show.'"><i class="'.$icon.'"></i> '.$key.'</th>';
            $target++;
        }

        $colDefs.='],';

        $return.='</tr></thead><tbody></tbody></table>';

        $a = "<'row' <'col-md-12'>>l<'row'<'col-md-6 col-sm-12'><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i>p<'col-md-7 col-sm-12'>>";

        $return.=
        '
            <script>
                $(document).ready(function(){
                    var SP = {"SearchTerm": "", "Page": 0, "Draw": 0};

                    var table = $("#'.$ID.'").DataTable({
                        "bDeferRender": true,
                        "columnDefs": [{"width":"100px", target:3}],
                        "lengthMenu":
                        [
                            [5,10,20,50,100,150,-1],
                            [5,10,20,50,100,150,"Tümü"]
                        ],
                        "order": [[1,"asc"]],
                        "processing": true,
                        "serverSide": true,
                        "responsive": true,
                        "sPaginationType":"bootstrap_full_number",
                        "ajax": {
                            "type": "POST",
                            "processData": false,
                            "url": "'.$ajaxurl.'",
                            "dataType": "json",
                            "contentType": "application/json; charset=utf-8",
                            "data": function (data) {
                                var info = (table == null) ? { page: 0, length: 10 } : table.page.info();
                                var search = (table == null) ? "" : table.search();

                                SP.Search = search;
                                SP.Page = info.page + 1;
                                SP.Draw = info.length;

                                return JSON.stringify(SP);
                            }
                        },
                        "dom": "'.$a.'",
                    });
                });
            </script>
        ';

        return $return;
    }

    protected function portlet(String $name = NULL, String $title = NULL, Array $_attributes = []) : String
    {
        $template = $this->getattr("template", $_attributes);

        $return = '
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet '.$template.' bordered" '.$this->attributes($_attributes).'>
                        <div class="portlet-title">
                            <div class="caption font-green-sharp">
                                <i class="icon-settings font-green-sharp"></i>
                                <span class="caption-subject bold uppercase">'.$title.'</span>
                            </div>
                        </div>
                        <div class="portlet-body">
        ';

        return $return;
    }

    public function textarea(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        if( ! isset($this->settings['attr']['name']) && ! empty($name) )
        {
            $this->settings['attr']['name'] = $name;
        }

        if( isset($this->settings['attr']['value']) )
        {
            $value = $this->settings['attr']['value'];
        }

        if( ! empty($this->settings['attr']['name']) )
        {
            if( isset($this->postback['bool']) && $this->postback['bool'] === true )
            {
                $method = ! empty($this->method) ? $this->method : $this->postback['type'];
                $value  = Validation::postBack($this->settings['attr']['name'], $method);

                $this->postback = [];
            }
        }

        $class = $this->getattr("class", $_attributes);
        $placeholder = $this->getattr("placeholder", $_attributes);

        $lb = $this->getattr("label", $_attributes);

        if(empty($label)) $label = $lb;

        $_attributes["name"] = $name;

        $_col = $this->getattr("col", $_attributes);
        $_col = !empty($_col) ? $_col : 4;

        $return = "";

        if($this->colsize == "0")
        {
            $return.="<div class='row'><div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }
        else
        {
            $return.="<div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }

        $return .= '
        <div class="form-group '.$this->formType.'">
            '.(!empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
            <textarea class="form-control '.$class.'" '.$this->attributes($_attributes).'>'.$value.'</textarea>
            '.(empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
        </div>';

        if(intval($this->colsize) >= 12)
        {
            $return.="</div></div>";
            $this->colsize = "0";
        }
        else
        {
            $return.="</div>";
        }

        return $return.EOL;
    }

    /*

    Örnek kullanım:

    $options = ["VALUE1" => "TEXT 1","VALUE2" => "TEXT 2", "SELECTED" => "VALUE2"];
    $trigger = ["ajaxurl" => "http://...", "target" => "#digerElement"];
    echo MForm::col(4)->trigger($trigger)->options($options)->id("cihaztanimi")->label("SELECT LABEL")->select2("SELECT NAME","");

    ->label() attr => Form'daki label tagına yazılan metin,
    ->options() attr => select elementinin optionsları (array değer alıyor. örnek kullanım yukarıdaki gibidir);
    ->trigger() attr => eleman seçildiğinde alt maddelerinin yükleneceği alan ve yükleneceği link belirtilir.

    */

    public function select2(String $name = NULL, String $label = NULL, Array $_attributes = []) : String
    {
        $opt = (Array)$this->getattr("options", $_attributes);
        $class = $this->getattr("class", $_attributes);
        $placeholder = $this->getattr("placeholder", $_attributes);

        $lb = $this->getattr("label", $_attributes);

        if(empty($label)) $label = $lb;

        $_attributes["name"] = $name;

        $_col = $this->getattr("col", $_attributes);
        $_col = !empty($_col) ? $_col : 4;

		$triggerForm = $this->getattr("triggerform", $_attributes);
        $triggerForm_html = "";

		if(!empty($triggerForm))
		{
			$jsonarr = json_encode($triggerForm["data"]);

			$triggerForm_html = '
				<script>
					$(document).on("change", ".'.$class.'", function(){
						App.blockUI();

						var form = '.$jsonarr.';

                        $.post("'.$triggerForm["ajaxurl"].'",{"value":$(this).val()},function(response){

							$.each(form, function(key, value){
								var target = value.target;
								var data = value.data;
								$(target).val(response[data]);
							});

                            App.unblockUI();

                        },"json").fail(function(xhr, status, error){
                            swal({
                                type:"danger",
                                text:error,
                                html:true,
                                title:"Beklenmedik bir hata oluştu"
                            });

							App.unblockUI();

                        });
					});
				</script>
			';
		}

        $trigger = $this->getattr("trigger", $_attributes);
        $trigger_html = "";

        if(empty($class)) $class = $this->uniqID();

        if(!empty($trigger))
        {
            $trigger_html .= '
                <script>
                    $(document).on("change", ".'.$class.'", function(){

                        App.blockUI();

                        $.post("'.$trigger["ajaxurl"].'",{"value":$(this).val()},function(data){

                            var nesne = $("'.$trigger["target"].'");
                            nesne.empty();
                            nesne.append(data);
                            nesne.select2("open");

                            App.unblockUI();

                        }).fail(function(xhr, status, error){
                            swal({
                                type:"danger",
                                text:error,
                                html:true,
                                title:"Beklenmedik bir hata oluştu"
                            });

							App.unblockUI();

                        });

                    });
                </script>
            ';
        }

        /**
         * Sadece ajax url barındırır, verilen link'e seçili value gönderimi yapar.
         * @var [String] => "http://..."
         */
        $openmodal = $this->getattr("openmodal", $_attributes);
        $openmodal_html = "";

        if(!empty($openmodal))
        {
            $openmodaldata = "";
            if(isset($openmodal["data"]))
            {
                $openmodal["data"]["value"] = '';
                $openmodaldata = Json::encode($openmodal["data"]);
            }
            else
            {
                $openmodal["data"]["value"] = '';
            }

            $openmodal_html .= '
                <script>
                    $("#globalModal").attr("close", "true");
                    $(document).on("change", ".'.$class.'", function(){

                        var data = '.$openmodaldata.';

                        data.value = $(this).val();

                        App.blockUI();

                        $.post("'.$openmodal["url"].'",data,function(data){

                            if(data != "false")
                            {
                                var modal = $("#globalModal", document);

                                modal.find(".modal-content").html(data);

                                modal.modal("show");

                                $(".select2").select2({width:"100%"});
                            }

                            App.unblockUI();

                        }).fail(function(xhr, status, error){
                            swal({
                                type:"danger",
                                text:error,
                                html:true,
                                title:"Beklenmedik bir hata oluştu"
                            });
                        });

                    });
                </script>
            ';
        }

        $return = "";

        if($this->colsize == "0")
        {
            $return.="<div class='row'><div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }
        else
        {
            $return.="<div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }

        $return .= '
        <div class="form-group '.$this->formType.'">
            '.(!empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
            <select class="form-control select2 '.$class.'" '.$this->attributes($_attributes).'>';

            $selected = "";

            if(isset($opt["SELECTED"]))
            {
                $selected = $opt["SELECTED"];
                unset($opt["SELECTED"]);
            }
            else if(isset($opt["selected"]))
            {
                $selected = $opt["SELECTED"];
                unset($opt["SELECTED"]);
            }

            foreach ($opt as $key => $value) {
                $return.='<option value="'.$key.'" '.($key == $selected ? "selected" : '').'>'.$value.'</option>';
            }

        $return.='
            </select>
            '.(empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
        </div>';

        if(intval($this->colsize) >= 12)
        {
            $return.="</div></div>";
            $this->colsize = "0";
        }
        else
        {
            $return.="</div>";
        }

        $return.=$trigger_html;
        $return.=$triggerForm_html;
        $return.=$openmodal_html;

        return $return.EOL;
    }

    public function select(String $name = NULL, String $label = NULL, Array $_attributes = []) : String
    {
        $opt = (Array)$this->getattr("options", $_attributes);
        $class = $this->getattr("class", $_attributes);
        $placeholder = $this->getattr("placeholder", $_attributes);
        $lb = $this->getattr("label", $_attributes);

        if(empty($label)) $label = $lb;

        $_attributes["name"] = $name;

        $_col = $this->getattr("col", $_attributes);
        $_col = !empty($_col) ? $_col : 4;

        $return = "";

        if($this->colsize == "0")
        {
            $return.="<div class='row'><div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }
        else
        {
            $return.="<div class='col-md-".$_col."'>";
            $this->colsize = (String)(intval($this->colsize) + intval($_col));
        }

        $return .= '
        <div class="form-group '.$this->formType.'">
            '.(!empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
            <select class="form-control '.$class.'" '.$this->attributes($_attributes).'>';

            $selected = "";

            if(isset($opt["SELECTED"]))
            {
                $selected = $opt["SELECTED"];
                unset($opt["SELECTED"]);
            }
            else if(isset($opt["selected"]))
            {
                $selected = $opt["SELECTED"];
                unset($opt["SELECTED"]);
            }

            foreach ($opt as $key => $value) {
                $return.='<option value="'.$key.'" '.($key == $selected ? "selected" : '').'>'.$value.'</option>';
            }

        $return.='
            </select>
            '.(empty($this->formType) ? '' : '<label class="control-label">'.$label.'</label>').'
        </div>';

        if(intval($this->colsize) >= 12)
        {
            $return.="</div></div>";
            $this->colsize = "0";
        }
        else
        {
            $return.="</div>";
        }

        return $return.EOL;
    }

    public function text(String $name = NULL, String $label = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $label, $_attributes, __FUNCTION__);
    }

    public function button(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function link(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function clearFix() : String
    {
        return "<div class='clearfix'></div>";
    }

    public function reset(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function submit(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function radio(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$settings = json_encode($_attributes);

        $html = $this->_input($name, $value, $_attributes, __FUNCTION__);

		$html .=
			"<script>
				$(document).ready(function(){
				  $('input').iCheck($settings);
				});
			</script>";

		return $html;
    }

    public function checkbox(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$settings = json_encode($_attributes);
		$html = $this->_input($name, $value, $_attributes, __FUNCTION__);

		$html .=
			"<script>
				$(document).ready(function(){
				  $('input').iCheck($settings);
				});
			</script>";

		return $html;
    }

    public function date(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$class = $this->getattr("class", $_attributes);
		$_attributes["class"] = $class;

		$settings = json_encode($_attributes);

		$html = $this->_input($name, $value, $_attributes, $class == "date" ? "text" : __FUNCTION__);

		$html .= "
			<script>
				$('.date').datepicker($settings);
			</script>
		";
        return $html;
    }

	// time class'ı verilmesi gerekiyor
    public function time(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$class = $this->getattr("class", $_attributes);
		$_attributes["class"] = $class;

		$settings = json_encode($_attributes);

		$html = $this->_input($name, $value, $_attributes, $class == "time" ? "text" : __FUNCTION__);

		$html .= "
			<script>
				$('.time').timepicker($settings);
			</script>
		";
        return $html;
    }

    public function datetime(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$class = $this->getattr("class", $_attributes);
		$_attributes["class"] = $class;

		$settings = json_encode($_attributes);

		$html = $this->_input($name, $value, $_attributes, $class == "datetime" ? "text" : __FUNCTION__);

		$html .= "
			<script>
				$('.datetime').datetimepicker($settings);
			</script>
		";
        return $html;
    }

    public function daterangepicker(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$class = $this->getattr("class", $_attributes);
		$_attributes["class"] = $class;

		$function = $this->getattr("function", $_attributes);
		unset($_attributes["function"]);

		$settings = json_encode($_attributes);

		$html = $this->_input($name, $value, $_attributes, $class == "daterangepicker" ? "text" : __FUNCTION__);

		$html .= "
			<script>
				$('.daterangepicker').daterangepicker($settings);
			</script>
		";
        return $html;
    }

    public function week(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function month(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function search(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function password(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function email(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function tel(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
		$html = $this->_input($name, $value, $_attributes, __FUNCTION__);

		$html.='<script> $("input[name=\''.$name.'\']").inputmask("0 (999) 999 99-99"); </script>';

        return $html;
    }

    public function number(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function url(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function image(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function color(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        return $this->_input($name, $value, $_attributes, __FUNCTION__);
    }

    public function hidden(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        if( isset($this->settings['attr']['name']) )
        {
            $name = $this->settings['attr']['name'];
        }

        if( isset($this->settings['attr']['value']) )
        {
            $value = $this->settings['attr']['value'];
        }

        $this->settings = [];

        $hiddens = NULL;

        $value = ( ! empty($value) )
                 ? 'value="'.$value.'"'
                 : "";

        // 1. parametre dizi ise
        if( is_array($name) ) foreach( $name as $key => $val )
        {
            $hiddens .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$val.'">'.EOL;
        }
        else
        {
            $hiddens =  '<input type="hidden" name="'.$name.'" id="'.$name.'" '.$value.'>'.EOL;
        }

        return $hiddens;
    }

    public function file(String $name = NULL, String $value = NULL, Array $_attributes = []) : String
    {
        if( ! empty($this->settings['attr']['multiple']) )
        {
            $multiple = true;
        }

        if( ! empty($this->settings['attr']['name']) )
        {
            $name = $this->settings['attr']['name'];
        }

        if( $multiple === true )
        {
            $this->settings['attr']['multiple'] = 'multiple';
            $name = suffix($name, '[]');
        }

        return $this->_input($name, '', $_attributes, 'file');
    }
}
