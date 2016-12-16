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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container   = $this->getContainer();
        $redis       = $container->get('snc_redis.default');
        $gameService = $container->get(Service::GAME);
        $pubSub      = $container->get(Service::PUBSUB);

        $now = time();

        $output->writeln("Now: $now");

        $output->writeln('Quering redis for affected game ids..');

        $ids = $redis->zrangebyscore('prevTurnTime', $now - self::T2, $now);

        $output->writeln('Affected game ids: ' . count($ids));

        foreach ($ids as $id)
        {
            $game            = $gameService->get($id);

            $nextTurn        = $game->nextTurn;
            $newNextTurn     = $game->getValidNextTurn(true);
            $prevTurnTime    = $game->prevTurnTime;
            $newPrevTurnTime = time();

            $redis->hmset(
                $id,

                'nextTurn',
                $newNextTurn,

                'prevTurnTime',
                $newPrevTurnTime
            );

            $pubSub->trigger(
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

        $output->writeln('Done.');
    }
}
