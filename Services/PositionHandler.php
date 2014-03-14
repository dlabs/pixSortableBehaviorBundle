<?php

namespace Pix\SortableBehaviorBundle\Services;

use Doctrine\ORM\EntityManager;

class PositionHandler
{
    const UP        = 'up';
    const DOWN      = 'down';
    const TOP       = 'top';
    const BOTTOM    = 'bottom';

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    public function getPosition($object, $position, $last_position)
    {
        switch ($position) {
            case 'up' :
                if ($object->getPosition() > 0) {
                    $position = $object->getPosition() - 1;
                }
                break;

            case 'down':
                if ($object->getPosition() < $last_position) {
                    $position = $object->getPosition() + 1;
                }
                break;

            case 'top':
                if ($object->getPosition() > 0) {
                    $position = 0;
                }
                break;

            case 'bottom':
                if ($object->getPosition() < $last_position) {
                    $position = $last_position;
                }
                break;
        }


        return $position;

    }

    public function getLastPosition($entity)
    {

        $query = $this->em->createQuery('SELECT MAX(m.position) FROM '.$entity.' m');
        $result = $query->getResult();

        if (array_key_exists(0, $result)) {
            return $result[0][1];
        }

        return 0;
    }

    public function setPositions($entity, $id, $position, &$callerAdmin)
    {
        $reOrder = function($bumperId, $bumperPos, $bumpedId, $bumpedPos) use($callerAdmin) {
            $bumper = $callerAdmin->getObject($bumperId);
            $bumper->setPosition($bumperPos);
            $callerAdmin->update($bumper);

            $bumped = $callerAdmin->getObject($bumpedId);
            $bumped->setPosition($bumpedPos);
            $callerAdmin->update($bumped);
        };

        $query = $this->em->createQuery('SELECT m.id, m.position FROM '.$entity.' m ORDER BY m.position ASC');
        $result = $query->getResult();

        $remapped = [];
        foreach ($result as $row) {
            $remapped[$row['id']] = $row['position'];
        }
        $remapped1 = array_flip($remapped);

        $moved =& $remapped[$id];
        $last = sizeof($remapped)-1;
        if ($position == self::DOWN && $moved + 1 <= $last) {
            $reOrder($id, ++$moved, $remapped1[$moved], --$remapped[$remapped1[$moved]]);

        } else if ($position == self::UP && $moved - 1 >= 0) {
            $reOrder($id, --$moved, $remapped1[$moved], ++$remapped[$remapped1[$moved]]);
        }

    }
}