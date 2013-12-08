<?php

namespace ClassCentral\SiteBundle\Repository;


use ClassCentral\SiteBundle\Entity\CourseStatus;
use Doctrine\ORM\EntityRepository;
use ClassCentral\SiteBundle\Entity\Offering;

class StreamRepository extends EntityRepository {

    /**
     * Used in the navabar
     */
    public function getCourseCountByStream()
    {

        $em = $this->getEntityManager();
        $result = $em->createQuery(
            'SELECT s.name as name, COUNT(DISTINCT c.id) AS total, s.slug as slug  FROM ClassCentralSiteBundle:Course c JOIN
             c.stream s  JOIN c.offerings o WHERE o.status != ' .Offering::COURSE_NA. ' GROUP BY c.stream ORDER BY total')
            ->getArrayResult();


        return $result;
    }

    public function getCourseCountBySubjects()
    {
        $em = $this->getEntityManager();
        $validStatusBound = CourseStatus::COURSE_NOT_SHOWN_LOWER_BOUND;
        $results = $em->createQuery(
            "SELECT s.id, s.name, count(DISTINCT c.id) as courseCount
             FROM ClassCentralSiteBundle:Stream s
             JOIN s.courses c
             WHERE c.status < $validStatusBound
             GROUP BY c.stream
             ORDER BY s.displayOrder ASC
            "
        )->getArrayResult();
        $subjects = array();
        foreach($results as $result)
        {
            $subjects[$result['id']] = $result;
        }

        return $subjects;
    }

    public function getSubjectsTree()
    {
        $em = $this->getEntityManager();
        $allSubjects = $em->getRepository('ClassCentralSiteBundle:Stream')->findAll();
        $subjects = array();
        foreach($allSubjects as $subject)
        {
            if($subject->getParentStream())
            {
                $childSubjects[$subject->getParentStream()->getId()][] = $subject;
            }
            else
            {
                $subjects[$subject->getSlug()] = array(
                    'name' => $subject->getName(),
                    'id' => $subject->getId(),
                    'slug'=> $subject->getSlug()
                );

                $children = array();
                foreach($subject->getChildren() as $childSub)
                {
                    $children[$childSub->getSlug()] = array(
                        'name' => $childSub->getName(),
                        'id' => $childSub->getId(),
                        'slug'=> $childSub->getSlug()
                    );

                }
                $subjects[$subject->getSlug()]['children'] = $children;
            }
        }

        return $subjects;
    }

}