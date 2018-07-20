<?php
/**
 * N11 PHP-SOAP Api
 * @author Bilal ATLI
 * @license MIT
 */
Class N11library {

    /**
     * @var string Api Anahtarı
     */
    private $appKey;

    /**
     * @var string Api Secret
     */
    private $appSecret;

    /**
     * @var array Method parametreleri
     */
    private $params;

    /**
     * @var SoapClient Soap İstemcisi
     */
    private $_soapClient;

    /**
     * @var bool Kütüphane Hata Ayıklama Modu
     */
    private $_debug;

    /**
     * @var object|null Son Hata Detaylarını Barındırır
     */
    private $_error = null;
    
    /**
     * Kütüphane Ayarlarını Tanımlar. Tüm işlemlerden önce çalıştırılması gerekir.
     * @param array|array $attributes 
     * @return null
     */
    public function __setOptions(array $attributes = array()) {
        $this->appKey = $attributes['appKey'];
        $this->appSecret = $attributes['appSecret'];
        $this->_debug = isset($attributes['_debug']) ? $attributes['_debug'] : false;
        $this->_params = ['auth' => ['appKey' => $this->appKey, 'appSecret' => $this->appSecret]];
    }
    
    /**
     * N11 Servis URL sini tanımlar
     * @param type $serviceURL 
     * @return type
     */
    public function _setService($serviceURL) {
        $this->_soapClient = new \SoapClient($serviceURL);
    }
 
    /**
     * Üst kategorileri verir
     * @return object
     */
    public function GetTopLevelCategories() {
        $this->_setService(N11Services::CATEGORY);
        return $this->_lookResponse($this->_soapClient->GetTopLevelCategories($this->_params));
    }

    /**
     * Alt kategorileri verir
     * @param int $categoryId Üst kategori ID Numarası
     * @return object
     */
    public function GetSubCategories($categoryId) {
        $this->_setService(N11Services::CATEGORY);
        $this->_params['categoryId'] = $categoryId;
        return $this->_lookResponse($this->_soapClient->GetSubCategories($this->_params));
    }
 
    /**
     * Şehirleri Verir
     * @return object
     */
    public function GetCities() {
        $this->_setService(N11Services::CITY);
        return $this->_lookResponse($this->_soapClient->GetCities($this->_params));
    }
 
    /**
     * Hesabınızda yüklenmiş olan ürün listesini verir
     * @param int $itemsPerPage Sayfa başı ürün sayısı
     * @param int $currentPage Şimdiki sayfa sayısı
     * @return object
     */
    public function GetProductList($itemsPerPage, $currentPage) {
        $this->_setService(N11Services::PRODUCT);
        $this->_params['pagingData'] = ['itemsPerPage' => $itemsPerPage, 'currentPage' => $currentPage];
        return $this->_lookResponse($this->_soapClient->GetProductList($this->_params));
    }
 
    /**
     * Satıcı koduna göre ürünü verir
     * @param string $sellerCode Ürün eklerken gönderdiğiniz koddur
     * @return object
     */
    public function GetProductBySellerCode($sellerCode) {
        $this->_setService(N11Services::PRODUCT);
        $this->_params['sellerCode'] = $sellerCode;
        return $this->_lookResponse($this->_soapClient->GetProductBySellerCode($this->_params));
    }
 
    /**
     * Ürün ekleme metodu
     * @param array|array $product [
     *      
     * ]
     * @return object
     */
    public function SaveProduct(array $product = Array()) {
        $this->_setService(N11Services::PRODUCT);
        $this->_params['product'] = $product;
        return $this->_lookResponse($this->_soapClient->SaveProduct($this->_params));
    }
 
    /**
     * Ürünün silme metodu
     * @param string $sellerCode 
     * @return object
     */
    public function DeleteProductBySellerCode($sellerCode) {
        $this->_setService(N11Services::PRODUCT);
        $this->_params['productSellerCode'] = $sellerCode;
        return $this->_lookResponse($this->_soapClient->DeleteProductBySellerCode($this->_params));
    }
    
    /**
     * Kapsamlı bilgiler ile sipariş listesini verir
     * @param array $searchData [buyerName,orderNumber,recipient,period = [startDate,endDate],sortForUpdateDate,productSellerCode,status,productId]
     * @return object
     */
    public function DetailedOrderList(array $searchData = Array()) {
        $this->_setService(N11Services::ORDER);
        $this->_params['searchData'] = $searchData;
        return $this->_lookResponse($this->_soapClient->DetailedOrderList($this->_params));
    }
    
    /**
     * Kapsamsız bilgiler ile sipariş listesini verir
     * @param array|array $searchData [buyerName,orderNumber,recipient,period = [startDate,endDate],sortForUpdateDate,productSellerCode,status,productId]
     * @return object
     */
    public function OrderList(array $searchData = Array()) {
        $this->_setService(N11Services::ORDER);
        $this->_params['searchData'] = $searchData;
        return $this->_lookResponse($this->_soapClient->OrderList($this->_params));
    }

    /**
     * ID Numarası girilen sipariş detaylarını verir
     * Kullanım : OrderDetail(['id' => $orderId])
     * @param array|array $orderRequest 
     * @return object
     */
    public function OrderDetail(array $orderRequest = Array()) {
        $this->_setService(N11Services::ORDER);
        $this->_params['orderRequest'] = $orderRequest;
        return $this->_lookResponse($this->_soapClient->OrderDetail($this->_params));
    }

    /**
     * İstek çıktısını inceleyerek eğer hata varsa ayıklar
     * @param object $response 
     * @return $response
     */
    public function _lookResponse($response) {
        if ($response->result->status == N11ResultStatus::FAIL) {
            $this->_error = (object)array(
                'code' => $response->result->errorCode,
                'message' => $response->result->errorMessage,
                'category' => $response->result->errorCategory,
            );
        }
        return $response;
    }

    /**
     * AppKey ve AppSecret doğruluğunu kontrol eder
     * @return bool
     */
    public function _checkConnect() {
        $getData = $this->GetTopLevelCategories();
        $getError = $this->getError();
        return !($getError->code === N11Exceptions::AUTH);
    }

    /**
     * Son hata kaydını verir
     * @return object
     */
    public function getError() {
        if ($this->_error === null) {
            return (object)array(
                'code' => N11Exceptions::NOERROR,
                'message' => '',
                'category' => null
            );
        }
        return $this->_error;
    }
 
    /**
     * Desctructor Method
     * @return null
     */
    public function __destruct() {
        if ($this->_debug) {
            print_r($this->_params);
        }
    }   
}

class N11Services {
    const CATEGORY = 'https://api.n11.com/ws/CategoryService.wsdl';
    const CITY = 'https://api.n11.com/ws/CityService.wsdl';
    const PRODUCT = 'https://api.n11.com/ws/ProductService.wsdl';
    const ORDER = 'https://api.n11.com/ws/OrderService.wsdl';
}

class N11ResultStatus {
    const SUCCESS = 'success';
    const FAIL = 'failure';
}

class N11Exceptions {
    const AUTH = 'SELLER_API.authenticationFailed';
    const NOERROR = '0';
}


/** 
    Save Product Param Example

    [
        'productSellerCode' => 'PI-19892718',
        'title' => 'Deneme Ürünü - Satın Almayın',
        'subtitle' => 'Deneme Ürünü - Satın Almayın - Subtitle',
        'description' => 'Deneme Ürünü - Satın Almayın - Description',
        'attributes' =>
        [
            'attribute' => Array()
        ],
        'category' =>
        [
            'id' => 1000000
        ],
        'price' => 2.00,
        'currencyType' => 'TL',
        'images' =>
        [
            'image' =>
            [
                'url' => 'http://image.url',
                'order' => 1
            ]
        ],
        'saleStartDate' => '',
        'saleEndDate' => '',
        'productionDate' => '',
        'expirationDate' => '',
        'productCondition' => '1',
        'preparingDay' => '3',
        'discount' => 10,
        'shipmentTemplate' => 'Alıcı Öder',
        'stockItems' =>
        [
            'stockItem' =>
            [
                'quantity' => 1,
                'sellerStockCode' => 'stokkodu',
                'attributes' =>
                [
                    'attribute' => []
                ],
                'optionPrice' => 2.00
            ]
        ]
    ]
**/