<?php

namespace App\Http\Services\PromoCode;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\PromoCode\PromoCodeResource;
use App\Models\PromoCode;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PromoCodeService
{
    protected $promoCodeRepo;
    public function __construct(PromoCodeRepository $promoCodeRepo)
    {
        $this->promoCodeRepo = $promoCodeRepo;
    }

    public function getAllPromoCodes($request)
    {
        $query = $this->promoCodeRepo->getAll($request);

        if ($request->per_page) {
            $promoCodes = new PaginationResource($query->paginate($request->per_page), PromoCodeResource::class);
        } else {
            $promoCodes = PromoCodeResource::collection($query->get());
        }
        return Response::successResponse($promoCodes, 'Promo Codes Retrieved Successfully');
    }

    public function getPromoCodeById($id)
    {
        try {
            $promoCode = $this->promoCodeRepo->find($id);

            if (!$promoCode) {
                return Response::errorResponse('Promo Code not found', [], 404);
            };

            return Response::successResponse(new PromoCodeResource($promoCode), 'Promo Code found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'Promo Code');
        } catch (\Exception $e) {
            return Response::handleException($e, 'retrieve promo code');
        }
    }

    public function createPromoCode(array $data)
    {
        try {
            $promoCode = $this->promoCodeRepo->create($data);

            return Response::successResponse(new PromoCodeResource($promoCode), 'Promo Code created successfully', 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create Promo Code');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create Promo Code');
        }
    }

    public function updatePromoCode($id, array $data)
    {
        try {
            $promoCode = $this->promoCodeRepo->update($id, $data);

            if (!$promoCode) {
                return Response::errorResponse('Promo Code not found', [], 404);
            }

            return Response::successResponse(new PromoCodeResource($promoCode), 'Promo `Code updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'Promo Code');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update Promo Code');
        }
    }

    public function deletePromoCode($id)
    {
        try {
            $promoCode = $this->promoCodeRepo->delete($id);

            if (!$promoCode) {
                return Response::errorResponse('Promo Code not found', [], 404);
            }

            return response()->successResponse(['is_success' => true], 'Promo Code deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return response()->handleModelNotFoundException($e, 'Promo Code');
        } catch (\Exception $e) {
            return response()->handleException($e, 'delete Promo Code');
        }
    }
}
