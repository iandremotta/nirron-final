<?php

namespace App\Repository;

use App\Entity\Solicitacao;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Solicitacao>
 *
 * @method Solicitacao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Solicitacao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Solicitacao[]    findAll()
 * @method Solicitacao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SolicitacaoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Solicitacao::class);
    }

    public function add(Solicitacao $entity, bool $flush = false, User $user = null): void
    {
        $entity->setUsuario($user);
        if (in_array(User::SOLICITANTE, $user->getRoles())) {
            $entity->setStatus(Solicitacao::STATUS_PENDENTE);
        } else if (in_array(User::APROVADOR_ADMINISTRATIVO, $user->getRoles()) || in_array(User::APROVADOR_OPERACIONAL, $user->getRoles())) {
            $entity->setStatus(Solicitacao::STATUS_APROVADOR_OK);
        }

        $entity->setCreatedAt(new \DateTime('now'));
        $entity->setUpdatedAt(new \DateTime('now'));
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Solicitacao[] Returns an array of Solicitacao objects
     */
    public function findSolicitacaoByUser(User $user)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.usuario = :val')
            ->setParameter('val', $user)
            ->orderBy('u.updatedAt', 'ASC')
            ->orderBy('u.status', 'ASC')
            ->orderBy('u.vencimento', 'DESC')
            ->getQuery();
    }


    /**
     * @return Solicitacao[] Returns an array of Solicitacao objects
     */
    public function findAllByTipoStatus($tipo = null, $status = 1)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.tipo = :tipo')
            ->andWhere('u.status = :status')
            ->setParameter('tipo', $tipo)
            ->setParameter('status', $status)
            ->orderBy('u.updatedAt', 'ASC')
            ->getQuery();
    }

    /**
     * @return Solicitacao[] Returns an array of Solicitacao objects
     */
    public function findAllByTipoStatusVencimento($tipo = null, $status = 1)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.tipo = :tipo')
            ->andWhere('u.status = :status')
            ->setParameter('tipo', $tipo)
            ->setParameter('status', $status)
            ->orderBy('u.vencimento', 'ASC')
            ->getQuery();
    }

    /**
     * @return Solicitacao[] Returns an array of Solicitacao objects
     */
    public function findAllByTipo($tipo)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.tipo = :tipo')
            ->setParameter('tipo', $tipo)
            ->orderBy('u.updatedAt', 'DESC')
            ->getQuery();
    }

    public function findAllByEmpresa($empresa)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.empresa = :empresa')
            ->andWhere('u.status > :status')
            ->setParameter('empresa', $empresa)
            ->setParameter('status', '2')
            ->orderBy('u.updatedAt', 'DESC')
            ->getQuery();
    }

    public function findAllByEmpresaStatus($empresa, $status)
    {

        return $this->createQueryBuilder('u')
            ->andWhere('u.empresa = :empresa')
            ->andWhere('u.status = :status')
            ->setParameter('empresa', $empresa)
            ->setParameter('status', $status)
            ->orderBy('u.vencimento', 'ASC')
            ->getQuery();
    }

    public function findAllByEmpresaStatusHoje($empresa, $status)
    {
        $hoje = date('Y-m-d 00:00:00');
        $amanha =  date('Y-m-d H:i:s', strtotime("+1 day", strtotime($hoje)));
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->andWhere(
                $qb->expr()->gte('u.vencimento', ':from'),
                $qb->expr()->lt('u.vencimento', ':to'),
            )
            ->andWhere($qb->expr()->eq('u.empresa', ':empresa'))
            ->andWhere($qb->expr()->eq('u.status', ':status'))
            ->setParameter('from', $hoje)
            ->setParameter('to', $amanha)
            ->setParameter('empresa', $empresa)
            ->setParameter('status', $status)
            ->orderBy('u.vencimento', 'DESC')
            ->getQuery();
    }

    public function findAllByEmpresaStatusSemana($empresa, $status)
    {
        $comecoSemana = date("Y-m-d H:i:s", strtotime('monday this week -1 day'));
        $fimSemana = date("Y-m-d H:i:s", strtotime('sunday this week +1 day'));
        $hoje = date('Y-m-d 00:00:00');
        $amanha =  date('Y-m-d H:i:s', strtotime("+1 day", strtotime($hoje)));
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->andWhere(
                $qb->expr()->gte('u.vencimento', ':from'),
                $qb->expr()->lt('u.vencimento', ':to'),
            )
            ->andWhere($qb->expr()->eq('u.empresa', ':empresa'))
            ->andWhere($qb->expr()->eq('u.status', ':status'))
            ->setParameter('from', $comecoSemana)
            ->setParameter('to', $fimSemana)
            ->setParameter('empresa', $empresa)
            ->setParameter('status', $status)
            ->orderBy('u.vencimento', 'DESC')
            ->getQuery();
    }

    public function findByBoleto($empresa, $status, $datas = array("01/01/2020"))
    {
        $dataFormated = array();
        foreach ($datas as $data) {
            $data = str_replace("/", "-", $data);
            $time = strtotime($data);
            $data = date('Y-m-d H:i:s', $time);
            array_push($dataFormated, $data);
        }

        if (count($datas) != 1) {
            return $this->createQueryBuilder('u')
                ->andWhere('u.empresa = :empresa')
                ->andWhere('u.status= :status')
                ->andWhere('u.vencimento BETWEEN :from AND :to')
                ->setParameter('empresa', $empresa)
                ->setParameter('status', $status)
                ->setParameter('from', $dataFormated[0])
                ->setParameter('to', date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dataFormated[1]))))
                ->orderBy('u.vencimento', 'DESC')
                ->getQuery();
        }
        $amanha = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dataFormated[0])));
        return $this->createQueryBuilder('u')
            ->andWhere('u.empresa = :empresa')
            ->andWhere('u.status = :status')
            ->andWhere('u.vencimento BETWEEN :from AND :to')
            ->setParameter('empresa', $empresa)
            ->setParameter('status', $status)
            ->setParameter('from', $dataFormated[0])
            ->setParameter('to', $amanha)
            ->orderBy('u.vencimento', 'DESC')
            ->getQuery();
    }


    public function update(Solicitacao $entity, bool $flush = false): void
    {
        $entity->setUpdatedAt(new \DateTime('now'));
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Solicitacao $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Solicitacao[] Returns an array of Solicitacao objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Solicitacao
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
