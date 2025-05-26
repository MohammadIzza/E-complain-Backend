<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;       

class ComplainReplyRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'content' => 'required|string|min:20|max:1000',
            'status' => Auth::user()->role == 'admin' 
                ? 'required|string|in:open,onprogres,resolved,rejected' 
                : 'nullable'
        ];
    }
}
