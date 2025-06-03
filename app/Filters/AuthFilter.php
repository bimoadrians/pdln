<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function __construct()
    {
        helper('url');
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
        if(!session()->get('akun_email')) {//kalo tidak punya akun username maka akan langsung redirect to login
            session()->set('url_pdln', current_url());
            session()->setFlashdata('warning', ['Silahkan login kembali']);
            return redirect()->to('');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
