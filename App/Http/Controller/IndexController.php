<?php
namespace App\Http\Controller;

use App\Model\Member;
use More\Src\Core\Http\Controller;
use More\Src\Lib\Database\Builder;

class IndexController extends Controller
{
    public function hello()
    {
        $name = $this->request()->get('name');
        $this->write("<h1>Hello! {$name}</h1>");
    }

    public function view()
    {
        $name = $this->request->get('name');
        $this->assign('name', $name);
        $this->fetch('index');
    }

    public function json()
    {
        // 注意：在 writeJson 方法与 write 方法不要在一次 response 中同时使用，否则会破坏数据的 json 格式
        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => 'json'
        ], 200);
    }

    public function db()
    {
        /*var_dump($this->app->db->getPrefix());

        $tableName = $this->app->db->tableName('member');

        $data = $this->app->db->selectOne('select * from ' . $tableName . ' where id = ?', [340]);*/

        $memberModel = new Member();

        $memberModel->find(1);

        //$data = $memberModel->where('signature', 'LIKE', '%一批%')->first();

        var_dump($memberModel->name);

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $memberModel->getData()
        ], 200);
    }

    public function container(Member $member)
    {
        $data = $member->find($this->request()->get('id'));

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data['name']
        ], 200);
    }

    public function model(Member $memberModel)
    {
        $memberModel->find(1);
        $memberModel->name = '小余';
        $memberModel->save();

        $data = $memberModel->count('id');

        $data2 = $memberModel->with('card', ['*'], function ($row, Builder $card) {
            $card->where('title', $row['name']);
        })->get();

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data,
            'data2' => $data2
        ], 200);
    }

    public function join(Member $memberModel)
    {
        /*$memberModel->where('t_user.id', 2)->leftJoin('card', 't_user.id', '=', 't_card.user_id')
            ->first();*/

        $memberModel->where('t_user.id', 1)->leftJoin('card', function (Builder $builder) {
            $builder->on('t_user.id', '=', 't_card.user_id')
                ->where('t_card.id', 1);
        })->first();

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $memberModel->title
        ], 200);
    }

    public function redis()
    {
        $cache = $this->app->cache;
        $result = $cache->set('name', 'Weekii');

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => [
                'result' => $result,
                'cache' => $cache->get('name')
            ]
        ], 200);
    }
}