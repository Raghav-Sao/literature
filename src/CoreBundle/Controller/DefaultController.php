<?php

namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class DefaultController extends Controller
{
    /**
     * @Route("/home", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //replace this example code with whatever you need
        // $total_card = array("5");
        // $a = 0;
        
        // while( $a<=52) {
        //     $a = 52;
        //     // $color = (int)(rand(1,52)/13);
        //     // $card_no = (int)(rand(1,52)/4);
        //     // $card = $color;
        //     // if (in_array($card, $total_card)) {
                
        //     // } else {
        //     //     array_push($card, $total_card);
        //     // }
        // }

        $redis = $this->container->get('snc_redis.default');
        // // $val = $redis->incr('foo:bar');
        // // $redis_cluster = $this->container->get('snc_redis.cluster');
        // // $val = $redis_cluster->get('ab:cd');
        // // $val = $redis_cluster->get('ef:gh');
        // // $val = $redis_cluster->get('ij:kl');
        // $val = $redis->get("name");
        $val = $redis->SET("jitu", "ffff");
        // $val = $redis->get("namef");
        $response = new Response("ddd", Response::HTTP_OK, array('content-type' => 'text/html'));
        return $response;
    }

}
