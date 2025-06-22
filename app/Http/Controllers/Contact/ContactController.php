<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Services\Contact\ContactService;

class ContactController extends Controller
{
    public $contactService;
    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index(ContactRequest $request)
    {
        return $this->contactService->getAllContactForms($request);
    }

    public function show(ContactRequest $request)
    {
        return $this->contactService->getContactFormById($request->id);
    }

    public function markAsChecked(ContactRequest $request, $id)
    {
        return $this->contactService->markContactAsChecked($request->checked, $id);
    }
    public function store(ContactRequest $request)
    {
        return $this->contactService->submitContactForm($request->validated());
    }

    public function destroy($id)
    {
        return $this->contactService->deleteContact($id);
    }
}
