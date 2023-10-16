<?php
namespace App;

use Aura\SqlQuery\QueryFactory;
use PDO;
class QueryBuilder
{
    private $pdo;
    private $queryFactory;
    private $data;
    public function __construct(PDO $pdo,QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
        $this->pdo = $pdo;
    }

    public function select($table,$data)
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols([$data])
            ->from($table);

        return $this->data;
    }
    public function getAll($table)
    {

        $select = $this->queryFactory->newSelect();
        $select->cols(['*'])
            ->from($table);


        $sth = $this->pdo->prepare($select->getStatement());


        $sth->execute($select->getBindValues());

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }

    public function insert($table,$data){

        $insert = $this->queryFactory->newInsert();
        $insert
            ->into($table)                   // INTO this table
            ->cols($data);
        $sth = $this->pdo->prepare($insert->getStatement());

        $sth->execute($insert->getBindValues());
    }

    public function update($data,$id,$table){

        $update = $this->queryFactory->newUpdate();

        $update
            ->table($table)                  // update this table
            ->cols($data)
            ->where('id = :id')
            ->bindValue('id', $id);
        // AND WHERE these conditions


        $sth = $this->pdo->prepare($update->getStatement());

// execute with bound values
        $sth->execute($update->getBindValues());
    }

    public function delete($id,$table)
    {
        $delete = $this->queryFactory->newDelete();

        $delete
            ->from($table)                   // FROM this table
            ->where('id = :id')           // AND WHERE these conditions      // OR WHERE these conditions
            ->bindValue('id', $id);

        $sth = $this->pdo->prepare($delete->getStatement());

        // execute with bound values
        $sth->execute($delete->getBindValues());
    }
    public function findOne($table,$id)
    {
        $select = $this->queryFactory->newSelect();

        $select->cols(['*'])
            ->from($table)
            ->where('id = :id')
            ->bindValue('id',$id);
        $sth = $this->pdo->prepare($select->getStatement());


        // bind the values and execute
        $sth->execute($select->getBindValues());


        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }
    public function find_by_email($table,$email)
    {
        $select = $this->queryFactory->newSelect();

        $select->cols(['*'])
            ->from($table)
            ->where('email = :email')
            ->bindValue('email',$email);
        $sth = $this->pdo->prepare($select->getStatement());


        // bind the values and execute
        $sth->execute($select->getBindValues());


        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function for_verification($table,$id){
        $select = $this->queryFactory->newSelect();

        $select->cols(['token','selector'])
            ->from($table)
            ->where('id = :id')
            ->bindValue('id',$id);
        $sth = $this->pdo->prepare($select->getStatement());


        // bind the values and execute
        $sth->execute($select->getBindValues());


        // get the results back as an associative array
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}

?>