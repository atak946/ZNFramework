# MForm Form Builder


>

#Contact US - Info:



> Author: Murat ATAK

> @: http://facebook.com/murat.msoft

> e: murat.atak.21@yandex.com


# Required

> [Bootstrap] : http://getbootstrap.com

> [Sweet Alert] : http://t4t5.github.io/sweetalert

> [DataTables] : https://datatables.net

> [Select2] : https://select2.github.io

> [JQUERY] : https://jquery.com

> [PHP]: https://php.net

> [Metronic assets] : http://keenthemes.com/preview/metronic


#USAGE:

```sh
echo MForm::attribute("value")->element("name","value");
```

## Two way usage

### First: 

```sh

echo MForm::[all html attributes]->element("name","value");

example:

echo MForm::id("element")->icon("fa fa-user")->[... ETC ...]->text("name","");

```

### And OTHER

```sh

echo MForm::element("name","value",[

"attribute-name" => "value",

"attribute-name2" => "value",

... ETC ...

]);

example:

echo MForm::text("name","value",[

"data-toggle" => "tooltip",
"icon" => "fa fa-user",
"id" => "TEXT",
"style" => "color:red",
... ETC ...
]);

```

## col and rows (auto closing row div step to col 12)

```sh
echo MForm::col(4)->text("text1","");
echo MForm::col(4)->text("text2","");
echo MForm::col(4)->text("text3","");
```

### OR 

```sh
echo MForm::col(6)->text("text1","");
echo MForm::col(6)->text("text2","");
```

## With icon input:

```sh
echo MForm::id("iconInput")->[..all html attributes..]->icon("fa fa-user")->iconalign("left")->text("name","default value");
```

## With icon button:

```sh
echo MForm::id("iconButton")->[..all html attributes..]->icon("fa fa-user")->iconalign("left")->button("name","default value");
```

## Create standart form

```sh
echo MForm::open("form");

.... OTHER ELEMENT ...

echo MForm::close();
```

## Form validation

```sh
echo MForm::validate(true)->open("form");
```

## Material form and validation

```sh
echo MForm::material(true)->validate(true)->open("form");
```

## Submit form with ajax and return callback on your custom area (sweet alert, modal, or any div)

```sh
echo  MForm::validate(true)->ajax([
                              "url"=>"http://..",
                              
                              "datatype"=>"JSON",
                              
                              "method"=>"POST",
                              
                              "callback"=>"4",
                              
                              "callbackurl"=> "http://.." 
                              
                              ])
                              
    ->material(false)->id("form1")->onsubmit("return false;")->open("form1");
```
                           
                           
### -> callback params:

> 1 => return message on alert alert-success, 

> 2 => clear body and set message on body,

> 3 => sweet alert, 

> 4 => #globalModal


### -> callbackurl: (for callback params : 4)

> for load custom modal content

## Validation rules

 * You'll write all validation rules 

```sh

echo MForm::required("")->min("5")->max("12")->text("number","");

```

## Open Portlets

```sh
echo MForm::template("light")->title("PORTLET TİTLE")->open("portlet");

.... OTHER ELEMENTS ....

echo MForm::close("portlet");
```

## clearFix (bootstrap class)

```sh
 echo MForm::clearFix();
```

## Checktext: check your value is already registered in your db, show error for user.

> Your php file will be return "true" or "false"

> " Class name is required "

```sh
 	echo MForm::checktext("http://myphpfile.php")->class("INPUT_CLASS")->label("USERNAME:")->text("USERNAME","");

	php code:

	DB:...()->..;

	if($recordisvalid)
		echo "true"; //record is valid, show error
	else
		echo "false"; //record is not valid, hide error

```

## Select2 with selected value and options

```sh
$opt = ["VAL1" => "TEXT1","VAL2" => "TEXT2", "SELECTED" => "TEXT2"];

echo MForm::options($opt)->id("SELECT2_ID")->label("SELECT2 LABEL")->select2("SELECT2_NAME","");
```

## Select2 with trigger (posting selected value to your ajax url and append new options in your target element)

```sh
$opt = ["VAL1" => "TEXT1","VAL2" => "TEXT2", "SELECTED" => "TEXT2"];

$triggerOptions = ["ajaxurl" => "POST URL", "target" => "#TARGET_SELECT2_FOR_LOAD_NEW_OPTIONS"];

echo MForm::trigger($triggerOptions)->options($opt)->id("SELECT2_ID")->label("SELECT2 LABEL")->select2("SELECT2_NAME","");
```

## TriggerForm for select2

```sh

$triggerForm =
			[
				"data" =>
				[
					0 => ["target" => ".ELEMENT1", "data" => "Array | Json Key"],
					1 => ["target" => ".ELEMENT2", "data" => "Array | Json Key"]
				],
				"ajaxurl" => "http://" //this link will be return $db->...()->row();
			];

SOURCE SELECT2:
echo MForm::col(4)->required("required")->triggerForm($triggerForm)->class("select2")->id("ID")->label("LABEL")->select2("NAME", "");

TARGET ELEMENTS:
echo MForm::col(4)->required("required")->class("ELEMENT1")->label("TARGET ELEMENT 1")->text("ELEMENT1","");

echo MForm::col(4)->required("required")->class("ELEMENT2")->label("TARGET ELEMENT 2")->text("ELEMENT2","");

```

## close 

```sh
echo MForm::close("element");

output:
</element>

echo MForm::close();

output:
</form>

echo MForm::count(2)->close("div");

output:
</div></div>
```
> element => html, div, span, p, h1, h3, ...

## Open custom modal on button click event

```sh
echo MForm::modal(
[
    "ajaxurl" => "Http://..", // link of your modal content
    "close" => "true", //AFTER SUBMİT CLOSE MODAL
    "refresh" => "false", // AFTER SUBMİT REFRESH PAGE
    "width" => "modal-lg", // MODAL SİZE
    "ajaxreload" => "true" // İF YOU USE DATATABLE SERVER SİDE THİS OPTİONS WİLL UPDATE YOUR TABLES
])
->id("BUTTON_ID")
->icon("fa fa-plus") //button icon
->class("btn btn-primary pull-right") //button class
->button("BUTTON_NAME", "BUTTON_TEXT");
```

## EXAMPLE MODAL CONTENT

```sh
<div class="modal-header">
	<button type="button" class="close" aria-hidden="true"></button>
	<h4 class="modal-title">TİTLE</h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="col-md-12">

			CONTENT

		</div>
	</div>
</div>
<div class="modal-footer">
	FOOTER BUTTONS ETC..
</div>

```

## What is the globalModal ?

> you need to include globalModal in your master page.
> MForm working with this modal skeleton

```sh
<div id="globalModal" data-backdrop="static" class="modal fade" tabindex="-1" data-width="400">
    <div class="modal-dialog">
        <div class="modal-content">

        </div>
    </div>
</div>
```

## How to create DataTable server side

> @ https://datatables.net/examples/data_sources/server_side.html

```sh
$cols = array
(
    "COL 1" 		=> array("ICON" => "fa fa-calendar"		  	, "SHOW" => "ALL"),
    "COL 2" 		=> array("ICON" => "fa fa-object-group"		, "SHOW" => "MOBILE") // DESKTOP
);

echo MForm::dom("") //Datatable buttons
    ->ajaxurl("server side url")
    ->table("table_1", $cols);

```

## TIME element usage:

```sh

echo MForm::col(4)->class("time")->label("TIME:")->icon("fa fa-calendar")->time("time", "", ["showMeridian" => "true" , ... OTHER ATTRIBUTES]);

```

## DATE element usage:

```sh

echo MForm::col(4)->class("date")->label("DATE:")->icon("fa fa-calendar")->date("date", "", ["endDate" => "+0d" , ... OTHER ATTRIBUTES]);

```

## DATETIME element usage:

```sh

echo MForm::col(4)->class("datetime")->label("DATE TIME:")->icon("fa fa-calendar")->datetime("datetime", "", ["endDate" => "+0d" , ... OTHER ATTRIBUTES]);

```

## DATERANGE element usage:

```sh

echo MForm::col(4)->class("daterangepicker")->label("DATE RANGE:")->icon("fa fa-calendar")->daterangepicker("daterangepicker", "", ["endDate" => "+0d" , ... OTHER ATTRIBUTES]);

```

## AND OTHER ELEMENTS ..

> echo MForm::hidden("name","value");

> echo MForm::select("name","value");

> echo MForm::email("name","value");

> echo MForm::tel("name","value");

> echo MForm::textarea("name","value");

> echo MForm::file("name","value");

> echo MForm::color("name","value");

> echo MForm::image("name","value");

> echo MForm::url("name","value");

> echo MForm::month("name","value");

> echo MForm::number("name","value");

> echo MForm::password("name","value");

> echo MForm::search("name","value");

> echo MForm::week("name","value");

> echo MForm::time("name","value");

> echo MForm::datetime("name","value");

> echo MForm::datetimeLocal("name","value");

> echo MForm::checkbox("name","value");

> echo MForm::radio("name","value");

> echo MForm::submit("name","value");

> echo MForm::reset("name","value");

> echo MForm::link("name","value");



