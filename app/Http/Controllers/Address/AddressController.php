<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\AddressRequest;
use App\Http\Services\Address\AddressService;

class AddressController extends Controller
{
    public $addressService;
    public function __construct(AddressService $addressService)
    {
        $this->middleware('auth:api');
        $this->addressService = $addressService;
//        "test";
    }

    public function index(AddressRequest $request)
    {
        return $this->addressService->getAllUserAddresses($request);
    }

    public function show(AddressRequest $request)
    {
        return $this->addressService->getAddressById($request->id);
    }

    public function store(AddressRequest $request)
    {
        return $this->addressService->createNewAddress($request->validated());
    }

    public function update(AddressRequest $request, $id)
    {
        return $this->addressService->updateAddress($id, $request->validated());
    }

    public function destroy(AddressRequest $request)
    {
        return $this->addressService->deleteAddress($request->id);
    }
}
