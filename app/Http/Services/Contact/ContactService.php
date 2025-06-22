<?php

namespace App\Http\Services\Contact;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Contact\ContactResource;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ContactService
{
    public function getAllContactForms($request)
    {
        $contact = Contact::query();

        if ($request->per_page) {
            $contact = new PaginationResource($contact->paginate($request->per_page), ContactResource::class);
        } else {
            $contact = ContactResource::collection($contact->get());
        }
        return Response::successResponse($contact, 'Contact Retrieved Successfully');

    }

    public function getContactFormById($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            return Response::successResponse(new ContactResource($contact), 'Contact found successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'Contact');
        } catch (\Exception $e) {
            return Response::handleException($e, 'retrieve contact');
        }
    }

    public function submitContactForm(array $data)
    {
        try {
            $contact = Contact::create($data);

            return Response::successResponse(new ContactResource($contact), 'Contact created successfully', 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create contact');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create contact');
        }
    }
    public function markContactAsChecked($checkedContact, $id)
    {
        try{
            $contact = Contact::findOrFail($id);
            $contact->update(['checked' => true]);

            return Response::successResponse(new ContactResource($contact), 'Contact updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Contact');

        } catch (\Exception $e) {
            return Response::handleException($e, 'update contact checking');
        }
    }
    public function deleteContact($id)
    {
        try {
            $contact = Contact::findOrFail($id);

            $contact->delete();

            return response()->successResponse(['is_success' => true], 'Contact deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return response()->handleModelNotFoundException($e, 'Contact');
        } catch (\Exception $e) {
            return response()->handleException($e, 'delete contact');
        }
    }
}
