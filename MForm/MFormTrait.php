<?php

trait MFormTrait
{
    //--------------------------------------------------------------------------------------------------------
    //
    // Author     : Ozan UYKUN <ozanbote@gmail.com>
    // Site       : www.znframework.com
    // License    : The MIT License
    // Copyright  : (c) 2012-2016, znframework.com
    //
    //--------------------------------------------------------------------------------------------------------

    //--------------------------------------------------------------------------------------------------------
    // FormElementsTrait
    //--------------------------------------------------------------------------------------------------------
    //
    // elements ...
    //
    //--------------------------------------------------------------------------------------------------------
    //use FormElementsTrait;

    //--------------------------------------------------------------------------------------------------------
    // HTMLElementsTrait
    //--------------------------------------------------------------------------------------------------------
    //
    // elements ...
    //
    //--------------------------------------------------------------------------------------------------------
    //use HTMLElementsTrait;

    //--------------------------------------------------------------------------------------------------------
    // $settings
    //--------------------------------------------------------------------------------------------------------
    //
    // Ayarları tutmak için
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $settings = [];

    //--------------------------------------------------------------------------------------------------------
    // $useElements
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $useElements =
    [
        'addclass' => 'class'
    ];

    protected $materialclass = "form-md-line-input";

    protected $formType = "";

    protected $colsize = "0";

    protected $form_id = NULL;

    protected $_validate = false;

    /*
        callback türleri:

        1 - Aynı sayfada üste bilgi mesajı yazacak
        2 - Aynı sayfaya içeriği temizleyip bilgi mesajını yazabilir
        3 - Sweet Alert içerisinde yazabilir.
        4 - Custom Modal İçerisinde yazabilir.

    */

    protected $validate_ajax = [];

    //--------------------------------------------------------------------------------------------------------
    // Magic Call
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $method
    // @param array  $parameters
    //
    //--------------------------------------------------------------------------------------------------------
    public function __call($method, $parameters)
    {
        $method = strtolower($method);

        if( empty($parameters) )
        {
            $parameters[0] = $method;
        }
        else
        {
            if( $parameters[0] === false )
            {
                return $this;
            }

            if( $parameters[0] === true )
            {
                $parameters[0] = $method;
            }
        }

        if( isset($this->useElements[$method]) )
        {
            $method = $this->useElements[$method];
        }

        $this->_element($method, ...$parameters);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Attributes
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $attributes
    //
    //--------------------------------------------------------------------------------------------------------
    public function attributes(Array $attributes) : String
    {
        $attribute = '';

        if( ! empty($this->settings['attr']) )
        {
            $attributes = array_merge($attributes, $this->settings['attr']);

            $this->settings['attr'] = [];
        }

        foreach( $attributes as $key => $values )
        {
            if( is_numeric($key) )
            {
                $attribute .= ' '.$values;
            }
            else
            {
                if( ! empty($key) )
                {
                    $attribute .= ' '.$key.'="'.$values.'"';
                }
            }
        }

        return $attribute;
    }

    //--------------------------------------------------------------------------------------------------------
    // Type
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $type
    // @param string $name
    // @param string $value
    // @param array  $attributes
    //
    //--------------------------------------------------------------------------------------------------------
    public function input(String $type = NULL, String $name = NULL, String $value = NULL, Array $attributes = []) : String
    {
        if( isset($this->settings['attr']['type']) )
        {
            $type = $this->settings['attr']['type'];

            unset($this->settings['attr']['type']);
        }

        $this->settings['attr'] = [];

        return $this->_input($name, $value, $attributes, $type);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Attributes
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $name
    // @param string $value
    // @param array  $attributes
    // @param string $type
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _input($name = "", $value = "", $attributes = [], $type = '')
    {
        if( $name !== '' )
        {
            $attributes['name'] = $name;
        }

        if( $value !== '' )
        {
            $attributes['value'] = $value;
        }

        if( ! empty($attributes['name']) )
        {
            if( isset($this->postback['bool']) && $this->postback['bool'] === true )
            {
                $method = ! empty($this->method) ? $this->method : $this->postback['type'];

                $attributes['value'] = \Validation::postBack($attributes['name'], $method);

                $this->postback = [];
            }
        }

        if(!isset($this->settings["attr"]["icon"]) && !isset($attributes["icon"]))
        {
            return $this->defaultText($name, $value, $attributes, $type);
        }
        else
        {
            $filter = ["text", "password", "date", "time", "datetime", "week", "month", "search", "email", "tel", "number", "number", "url"];

            if(in_array($type, $filter))
                return $this->textIcon($name, $value, $attributes, $type);
            else if(in_array($type, ["submit", "button"]))
            {
                return $this->_button($name, $value, $attributes, $type);
            }
            else if($type == "link")
            {
                $icon = $this->getattr("icon", $attributes);
                $iconAlign = $this->getattr("iconalign", $attributes);
                $value = $this->getattr("value", $attributes);

                $in = $iconAlign == "left" || empty($iconAlign) ? "<i class='".$icon."'></i>".$value : $value."<i class='".$icon."'></i>";

                return "<a ".$this->attributes($attributes).">".$in."</a>";
            }
        }
    }

    protected function _button(String $name = "", String $value = "", Array $attributes = [], String $type = '') : String
    {
        $icon = $this->getattr("icon", $attributes);
        $iconAlign = $this->getattr("iconalign", $attributes);
        $value = $this->getattr("value", $attributes);
        $class = $this->getattr("class", $attributes);
        $modal = $this->getattr("modal", $attributes);
        $modal_html = "";

        $_uniqclass = $this->uniqID();

        if(!empty($modal))
        {
            $ajaxurl = $modal["ajaxurl"];
            $close = $modal["close"];
            $refresh = $modal["refresh"];
            $width = $modal["width"];
            $ajaxreload = $modal["ajaxreload"];


            $modal_html = "
            <script>

                $(document).on('click', '.{$_uniqclass}', function(){

                    App.blockUI();

                    $.post('{$ajaxurl}', function(response){

                        var modal = $('#globalModal', document);

                        modal.find('.modal-content').html(response);
                        modal.attr('close', '{$close}');
                        modal.attr('refresh', '{$refresh}');
                        modal.attr('ajaxreload', '{$ajaxreload}');
                        //modal.addClass('{$width}');
                        modal.find('.modal-dialog').addClass('{$width}');

                        App.unblockUI();
                        modal.modal('show');
                        $('.select2').select2({width:'100%'});

                    });

                });

            </script>";
        }

        unset($attributes['name']);
        $name = $this->getattr("name", $attributes);

        $in = $iconAlign == "left" || empty($iconAlign) ? "<i class='".$icon."'></i> ".$value : $value." <i class='".$icon."'></i>";

        return "<button type='$type' class='btn ".$class." ".$_uniqclass."' ".$this->attributes($attributes).">".$in."</button>".$modal_html;
    }

    protected function defaultText(String $name = "", String $value = "", Array $attributes = [], String $type = '') : String
    {
        $value = $this->getattr("value", $attributes);
        $label = $this->getattr("label", $attributes);
        $class = $this->getattr("class", $attributes);

        if(empty($class)) $class = $this->uniqID();

        $_col = $this->getattr("col", $attributes);
        $_col = !empty($_col) ? $_col : 4;

        $this->_checkText($class, $attributes);

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

        $return .= '<div class="form-group '.$this->formType.'">';

        if(!empty($this->formType))
        {
            $return.='<input class="form-control '.$class.'" value="'.$value.'" type="'.$type.'"'.$this->attributes($attributes).'>';
            $return.='<label for="form_control_1">'.$label.'</label>';
        }
        else
        {
            $return.='<label for="form_control_1">'.$label.'</label>';
            $return.='<input class="form-control '.$class.'" value="'.$value.'" type="'.$type.'"'.$this->attributes($attributes).'>';
        }

        $return .= '</div>';

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

    protected function textIcon(String $name = "", String $value = "", Array $attributes = [], String $type = '') : String
    {
        $icon = $this->getattr("icon", $attributes);
        $iconAlign = $this->getattr("iconalign", $attributes);
        $value = $this->getattr("value", $attributes);
        $label = $this->getattr("label", $attributes);
        $class = $this->getattr("class", $attributes);

        if(empty($class)) $class = $this->uniqID();

        $_col = $this->getattr("col", $attributes);
        $_col = !empty($_col) ? $_col : 4;

        $this->_checkText($class, $attributes);

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

        $return .= '<div class="form-group '.$this->formType.'">';

        $span = '<div class="input-icon"><i class="'.$icon.'"></i>';

        $input = '<input type="'.$type.'" value="'.$value.'" class="form-control '.$class.'" '.$this->attributes($attributes).'>';

        if(!empty($this->formType))
        {
            if($iconAlign == "left" || empty($iconAlign))
            {
                $return.= $span.' '.$input;
            }
            else
                $return.= $input.' '.$span;

            $return.='<label for="form_control_1">'.$label.'</label>';
        }
        else
        {
            $return.='<label for="form_control_1">'.$label.'</label>';

            if($iconAlign == "left" || empty($iconAlign))
            {
                $return.= $span.' '.$input;
            }
            else
                $return.= $input.' '.$span;
        }

        $return .= '</div></div>';

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

    protected function getattr(String $name = NULL, Array $_attr = [])
    {
        $return = "";

        if(empty($name)) return false;

        if(isset($_attr[$name]))
        {
            $return = $_attr[$name];
            unset($_attr[$name]);
        }
        else if(isset($this->settings["attr"][$name]))
        {
            $return = $this->settings["attr"][$name];
            unset($this->settings["attr"][$name]);
        }
        else
            return "";

        return $return;
    }

    protected function _checkText(String $class = "", Array $_attributes = [])
    {
        $checktext = $this->getattr("checktext", $_attributes);

        if(!empty($checktext))
        {
            $url = $checktext;

            echo '
                <script>
                    $(".'.$class.'", document).attr("checktext","");

                    $(document).on("input keypress paste", ".'.$class.'", function(){
                        var nesne = $(this);
                        $.ajax({
                            url: "'.$url.'",
                            type:"post",
                            dataType:"",
                            data: {"value":nesne.val()},
                            success:function(response){
                                console.log(response);
                                if(response == "true")
                                {
                                    nesne.closest(".form-group").addClass("has-error");
                                    nesne.closest(".form-group").removeClass("has-success");
                                }
                                else{
                                    nesne.closest(".form-group").removeClass("has-error");
                                    nesne.closest(".form-group").addClass("has-success");
                                }
                            }
                        })
                    });
                </script>
            ';
        }
        else
        {
            echo "";
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // protected _element()
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $function
    // @param string $element
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _element($function, $element)
    {
        $this->settings['attr'][strtolower($function)] = $element;
    }

    protected function getValidateAttr(String $name = NULL) : String
    {
        if(isset($this->validate_ajax[$name]))
        {
            return $this->validate_ajax[$name];
        }
        else
            return "";
    }

    protected function uniqID()
    {
        return Encode::super(md5(uniqid(mt_rand(), true)));
    }

}
