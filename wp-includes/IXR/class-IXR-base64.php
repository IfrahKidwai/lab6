<?php
 class IXR_Base64 { var $data; function __construct( $data ) { $this->data = $data; } public function IXR_Base64( $data ) { self::__construct( $data ); } function getXml() { return '<base64>'.base64_encode($this->data).'</base64>'; } } 