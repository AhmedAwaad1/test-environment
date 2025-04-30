<?php

namespace App\Http\Services\Address;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Address\AddressResource;
use App\Repositories\Address\AddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AddressService
{
    protected $addressRepo;

    public function __construct(AddressRepository $addressRepo)
    {
        $this->addressRepo = $addressRepo;
    }


    public function getAllUserAddresses($request)
    {
        $user = Auth::user();
        $request['user_id'] = $user->id;

        $query = $this->addressRepo->getAll($request);

        if ($request->per_page) {
            $addresses = new PaginationResource($query->paginate($request->per_page), AddressResource::class);
        } else {
            $addresses = AddressResource::collection($query->get());
        }

        return Response::successResponse($addresses, 'addresses retrieved successfully');
    }

    public function getAddressById($addressId)
    {
        try {
            $user = Auth::user();
            $request['user_id'] = $user->id;

            $address = $this->addressRepo->find($addressId, $request['user_id']);

            if (!$address) {
                return Response::errorResponse('address not found', [], 404);
            }

            return Response::successResponse(new AddressResource($address), 'address found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'address');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve address');
        }
    }

    public function createNewAddress($request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $request['user_id'] = $user->id;

            $address = $this->addressRepo->create($request);

            if($request['is_default'] == 1){
                $this->addressRepo->updateDefaultAddress($user->id, $address->id);
            }

            DB::commit();

            return Response::successResponse(new AddressResource($address), 'address created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'create address');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'create address');
        }
    }

    public function updateAddress($id, array $data)
    {
        try {
            $user = Auth::user();
            $data['user_id'] = $user->id;

            $address = $this->addressRepo->update($id, $data);

            if (!$address) {
                return Response::errorResponse('Address not found', [], 404);
            }

            if($data['is_default'] == 1){
                $this->addressRepo->updateDefaultAddress($user->id, $address->id);
            }

            return Response::successResponse(new AddressResource($address), 'address updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'address');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update address');
        }
    }

    public function deleteAddress($id)
    {
        $user = Auth::user();
        $userId = $user->id;

        $address = $this->addressRepo->delete($id, $userId);

        if (!$address) {
            return Response::errorResponse('address not found', [], 404);
        }

        return Response::successResponse(['is_success' => 1], 'address deleted successfully');
    }

    public function updateDefaultAddress($userId, $addressId)
    {
        try {
            $address = $this->addressRepo->updateDefaultAddress($userId, $addressId);
            return Response::successResponse(new AddressResource($address), 'address updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'address');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update address');
        }
    }
}
