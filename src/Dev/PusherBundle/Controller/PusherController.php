<?php

namespace Dev\PusherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PusherController extends Controller
{
    public function presenceAuthAction(Request $request)
    {
        $this->get('dev_pusher.service')->presenceChannelsAuth($request->request->get('socket_id'));
        return new Response();
    }
}
