<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Constant\Service;
use AppBundle\Constant\Game\Event;

class UpdateGameNextTurnCommand extends ContainerAwareCommand
{
    const NAME = 'app:updateGameNextTurn';
    const DESC = 'Updates game\'s next turn periodically.';
    const HELP = '
        - Game has prevTurn, prevTurnTime and nextTurn.
        - Every T1 sec, this commands will run and find all active games with
          prevTurnTime - now > T2 and updates nextTurn to prevTurn.
    ';

    const T1 = 30;
    const T2 = 30;

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESC)
            ->setHelp(self::HELP);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container         = $this->getContainer();

        $this->logger      = $container->get('logger');
        $this->redis       = $container->get('snc_redis.default');
        $this->gameService = $container->get(Service::GAME);
        $this->pubSub      = $container->get(Service::PUBSUB);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = time();

        $this->logger->debug('Cron starts.', [
                'cron'   => 'app:updateGameNextTurn',
                'time'   => $now,
                'status' => 'start',
            ]);

        $ids = $this->redis->zrangebyscore('prevTurnTime', $now - self::T2, $now);

        foreach ($ids as $id)
        {
            $this->do($id);
        }

        $this->logger->debug('Cron ends.', [
                'cron'   => 'app:updateGameNextTurn',
                'time'   => $now,
                'status' => 'end',
                'ids'    => $ids,
            ]);
    }

    protected function do(string $id)
    {
        $game            = $this->gameService->get($id);

        $nextTurn        = $game->nextTurn;
        $newNextTurn     = $game->getValidNextTurn(true);
        $prevTurnTime    = $game->prevTurnTime;
        $newPrevTurnTime = time();

        $this->redis->hmset(
            $id,

            'nextTurn',
            $newNextTurn,

            'prevTurnTime',
            $newPrevTurnTime
        );

        $this->pubSub->trigger(
            $game->id,
            Event::GAME_NEXT_TURN_UPDATE,
            [
                'nextTurn'        => $nextTurn,
                'newNextTurn'     => $newNextTurn,
                'prevTurnTime'    => $prevTurnTime,
                'newPrevTurnTime' => $newPrevTurnTime,
            ]
        );
    }
}
