<?php

namespace app\modules\pelaporan\controllers;

use Yii;
use app\models\TaSPJ;
use app\modules\penatausahaan\models\TaSPJSearch;
use app\models\TaSP3B;
use app\modules\pelaporan\models\TaSP3BSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SpjController implements the CRUD actions for TaSPJ model.
 */
class Sp3bController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'assign' => ['POST'],
                    'status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all TaSPJ models.
     * @return mixed
     */
    public function actionIndex()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        $searchModel = new TaSP3BSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['tahun' => $Tahun]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'Tahun' => $Tahun,
        ]);
    }

    /**
     * Displays a single TaSPJ model.
     * @param string $tahun
     * @param string $no_spj
     * @return mixed
     */
    public function actionView($tahun, $no_spj)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }   
        return $this->renderAjax('view', [
            'model' => $this->findModel($tahun, $no_spj),
        ]);
    }

    public function actionPrint($tahun, $no_sp3b)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $references = \app\models\TaTh::findOne(['tahun' => $tahun]);
        $model = $this->findModel($tahun, $no_sp3b);
        $tgl_sp3b = $model->tgl_sp3b;
        $spjdata = \Yii::$app->db->createCommand("
            SELECT
            -- a.tahun,
            -- c.no_sp3b,
            -- a.sekolah_id,
            a.kd_program,
            g.uraian_program,
            a.kd_sub_program,
            f.uraian_sub_program,
            a.kd_kegiatan,
            e.uraian_kegiatan,
            a.Kd_Rek_1,
            a.Kd_Rek_2,
            a.Kd_Rek_3,
            a.Kd_Rek_4,
            a.Kd_Rek_5,
            d.Nm_Rek_5,
            h.sumber_dana,
            h.abbr,
            SUM(a.nilai) AS nilai
            FROM
            ta_spj_rinc AS a
            INNER JOIN ta_sp3b_rinc AS b ON a.tahun = b.tahun AND a.no_spj = b.no_spj AND a.sekolah_id = b.sekolah_id
            INNER JOIN ta_sp3b AS c ON b.tahun = c.tahun AND b.no_sp3b = c.no_sp3b
            INNER JOIN ref_rek_5 AS d ON d.Kd_Rek_1 = a.Kd_Rek_1 AND d.Kd_Rek_2 = a.Kd_Rek_2 AND d.Kd_Rek_3 = a.Kd_Rek_3 AND d.Kd_Rek_4 = a.Kd_Rek_4 AND d.Kd_Rek_5 = a.Kd_Rek_5
            INNER JOIN ref_kegiatan_sekolah AS e ON e.kd_program = a.kd_program AND e.kd_sub_program = a.kd_sub_program AND e.kd_kegiatan = a.kd_kegiatan
            INNER JOIN ref_sub_program_sekolah AS f ON e.kd_program = f.kd_program AND e.kd_sub_program = f.kd_sub_program
            INNER JOIN ref_program_sekolah AS g ON f.kd_program = g.kd_program
            INNER JOIN (
                SELECT
                a.tahun,
                a.sekolah_id,
                a.kd_program,
                a.kd_sub_program,
                a.kd_kegiatan,
                a.Kd_Rek_1,
                a.Kd_Rek_2,
                a.Kd_Rek_3,
                a.Kd_Rek_4,
                a.Kd_Rek_5,
                b.pengesahan,
                b.kd_penerimaan_1,
                b.kd_penerimaan_2,
                b.uraian AS sumber_dana,
                b.abbr,
                SUM(a.total) AS total
                FROM
                ta_rkas_history AS a
                INNER JOIN ref_penerimaan_sekolah_2 AS b ON a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2
                WHERE b.pengesahan = 1 AND perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE sekolah_id = a.sekolah_id AND tgl_peraturan <= '$tgl_sp3b')
                GROUP BY a.tahun,
                a.sekolah_id,
                a.kd_program,
                a.kd_sub_program,
                a.kd_kegiatan,
                a.Kd_Rek_1,
                a.Kd_Rek_2,
                a.Kd_Rek_3,
                a.Kd_Rek_4,
                a.Kd_Rek_5,
                b.pengesahan,
                b.kd_penerimaan_1,
                b.kd_penerimaan_2,
                b.uraian,
                b.abbr
            ) h ON a.tahun = h.tahun AND a.sekolah_id = h.sekolah_id AND a.kd_program = h.kd_program AND a.kd_sub_program = h.kd_sub_program AND a.kd_kegiatan = h.kd_kegiatan
            AND a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 = h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5            
            WHERE b.no_sp3b = '$no_sp3b'
            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, h.sumber_dana, h.abbr
            ORDER BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5
            ");
        $data = $spjdata->queryAll();
            
        $saldobank = NULL;
        $saldokas = NULL;
        $pendapatan = NULL;
        $belanja = NULL;
        //find all spj atas sp2b ini
        $bukti = $this->findBukti($tahun, $no_sp3b);
        foreach($bukti AS $bukti){
            // find tgl_spj dan Saldo Awal
            $query = \Yii::$app->db->createCommand("SELECT MAX(tgl_spj) AS tgl_spj FROM ta_spj WHERE tahun = $tahun AND sekolah_id = ".$bukti->spj->sekolah_id." AND tgl_spj < '".$bukti->spj->tgl_spj."'");
            $tglsaldo = $query->queryOne()['tgl_spj'];
            IF($tglsaldo == NULL) $tglsaldo = $tahun.'-01-01';
            $query = \Yii::$app->db->createCommand("call sisa_kas($tahun, ".$bukti->spj->sekolah_id.", 1,'$tglsaldo')");
            $saldobank = $saldobank + $query->queryOne()['nilai'];
            $query = \Yii::$app->db->createCommand("call sisa_kas($tahun, ".$bukti->spj->sekolah_id.", 2,'$tglsaldo')");
            $saldokas = $saldokas + $query->queryOne()['nilai'];            

            // Find Pendapatan
            $query = \Yii::$app->db->createCommand("            
                SELECT
                SUM(a.nilai) AS nilai
                FROM
                ta_spj_rinc AS a
                INNER JOIN ta_sp3b_rinc AS b ON a.tahun = b.tahun AND a.no_spj = b.no_spj AND a.sekolah_id = b.sekolah_id
                INNER JOIN ta_sp3b AS c ON b.tahun = c.tahun AND b.no_sp3b = c.no_sp3b
                INNER JOIN ref_rek_5 AS d ON d.Kd_Rek_1 = a.Kd_Rek_1 AND d.Kd_Rek_2 = a.Kd_Rek_2 AND d.Kd_Rek_3 = a.Kd_Rek_3 AND d.Kd_Rek_4 = a.Kd_Rek_4 AND d.Kd_Rek_5 = a.Kd_Rek_5
                INNER JOIN ref_kegiatan_sekolah AS e ON e.kd_program = a.kd_program AND e.kd_sub_program = a.kd_sub_program AND e.kd_kegiatan = a.kd_kegiatan
                INNER JOIN ref_sub_program_sekolah AS f ON e.kd_program = f.kd_program AND e.kd_sub_program = f.kd_sub_program
                INNER JOIN ref_program_sekolah AS g ON f.kd_program = g.kd_program
                INNER JOIN (
                    SELECT
                    a.tahun,
                    a.sekolah_id,
                    a.kd_program,
                    a.kd_sub_program,
                    a.kd_kegiatan,
                    a.Kd_Rek_1,
                    a.Kd_Rek_2,
                    a.Kd_Rek_3,
                    a.Kd_Rek_4,
                    a.Kd_Rek_5,
                    b.pengesahan,
                    b.kd_penerimaan_1,
                    b.kd_penerimaan_2,
                    b.uraian AS sumber_dana,
                    b.abbr,
                    SUM(a.total) AS total
                    FROM
                    ta_rkas_history AS a
                    INNER JOIN ref_penerimaan_sekolah_2 AS b ON a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2
                    WHERE b.pengesahan = 1 AND perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE sekolah_id = a.sekolah_id AND tgl_peraturan <= '$tgl_sp3b')
                    GROUP BY a.tahun,
                    a.sekolah_id,
                    a.kd_program,
                    a.kd_sub_program,
                    a.kd_kegiatan,
                    a.Kd_Rek_1,
                    a.Kd_Rek_2,
                    a.Kd_Rek_3,
                    a.Kd_Rek_4,
                    a.Kd_Rek_5,
                    b.pengesahan,
                    b.kd_penerimaan_1,
                    b.kd_penerimaan_2,
                    b.uraian,
                    b.abbr
                ) h ON a.tahun = h.tahun AND a.sekolah_id = h.sekolah_id AND a.kd_program = h.kd_program AND a.kd_sub_program = h.kd_sub_program AND a.kd_kegiatan = h.kd_kegiatan
                AND a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 = h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5            
                WHERE b.no_sp3b = '$no_sp3b' AND a.no_spj = '".$bukti->no_spj."' AND a.kd_rek_1 = 4
                GROUP BY a.no_spj
                ");
            $pendapatan += $query->queryOne()['nilai'];

            // Find Belanja
            $query = \Yii::$app->db->createCommand("            
                SELECT
                SUM(a.nilai) AS nilai
                FROM
                ta_spj_rinc AS a
                INNER JOIN ta_sp3b_rinc AS b ON a.tahun = b.tahun AND a.no_spj = b.no_spj AND a.sekolah_id = b.sekolah_id
                INNER JOIN ta_sp3b AS c ON b.tahun = c.tahun AND b.no_sp3b = c.no_sp3b
                INNER JOIN ref_rek_5 AS d ON d.Kd_Rek_1 = a.Kd_Rek_1 AND d.Kd_Rek_2 = a.Kd_Rek_2 AND d.Kd_Rek_3 = a.Kd_Rek_3 AND d.Kd_Rek_4 = a.Kd_Rek_4 AND d.Kd_Rek_5 = a.Kd_Rek_5
                INNER JOIN ref_kegiatan_sekolah AS e ON e.kd_program = a.kd_program AND e.kd_sub_program = a.kd_sub_program AND e.kd_kegiatan = a.kd_kegiatan
                INNER JOIN ref_sub_program_sekolah AS f ON e.kd_program = f.kd_program AND e.kd_sub_program = f.kd_sub_program
                INNER JOIN ref_program_sekolah AS g ON f.kd_program = g.kd_program
                INNER JOIN (
                    SELECT
                    a.tahun,
                    a.sekolah_id,
                    a.kd_program,
                    a.kd_sub_program,
                    a.kd_kegiatan,
                    a.Kd_Rek_1,
                    a.Kd_Rek_2,
                    a.Kd_Rek_3,
                    a.Kd_Rek_4,
                    a.Kd_Rek_5,
                    b.pengesahan,
                    b.kd_penerimaan_1,
                    b.kd_penerimaan_2,
                    b.uraian AS sumber_dana,
                    b.abbr,
                    SUM(a.total) AS total
                    FROM
                    ta_rkas_history AS a
                    INNER JOIN ref_penerimaan_sekolah_2 AS b ON a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2
                    WHERE b.pengesahan = 1 AND perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE sekolah_id = a.sekolah_id AND tgl_peraturan <= '$tgl_sp3b')
                    GROUP BY a.tahun,
                    a.sekolah_id,
                    a.kd_program,
                    a.kd_sub_program,
                    a.kd_kegiatan,
                    a.Kd_Rek_1,
                    a.Kd_Rek_2,
                    a.Kd_Rek_3,
                    a.Kd_Rek_4,
                    a.Kd_Rek_5,
                    b.pengesahan,
                    b.kd_penerimaan_1,
                    b.kd_penerimaan_2,
                    b.uraian,
                    b.abbr
                ) h ON a.tahun = h.tahun AND a.sekolah_id = h.sekolah_id AND a.kd_program = h.kd_program AND a.kd_sub_program = h.kd_sub_program AND a.kd_kegiatan = h.kd_kegiatan
                AND a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 = h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5            
                WHERE b.no_sp3b = '$no_sp3b' AND a.no_spj = '".$bukti->no_spj."' AND a.kd_rek_1 = 5
                GROUP BY a.no_spj
                ");
            $belanja += $query->queryOne()['nilai'];            
        }
        $saldoawal = $saldobank + $saldokas;

        return $this->render('print', [
            'model' => $model,
            'data' => $data,
            'bukti' => $bukti,
            'saldoawal' => $saldoawal,
            'pendapatan' => $pendapatan,
            'belanja' => $belanja,
            'ref' => $references,
        ]);
    }    

    public function actionSpjbukti($tahun, $no_sp3b)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        $model = $this->findModel($tahun, $no_sp3b);
        $sp3brinc_lalu = \app\models\TaSP3BRinc::find()->where(['tahun' => $Tahun])->andWhere('no_sp3b <> \''.$model->no_sp3b.'\'')->all();
        // This code suppose to select all previous SPJ not in any SP3B but any sekolah_id already in this SP3B. Only one sekolah_id in one SP3B. If more than one sekolah_id in an SP3B, it will doubled Saldo Awal
        // $SpjSp3bSekolah = \app\models\TaSP3BRinc::find()->where(['tahun' => $Tahun])->andWhere('no_sp3b <> \''.$model->no_sp3b.'\'')->andWhere('sekolah_id NOT IN ()')->all();
        $no_spjs = [];
        foreach ($sp3brinc_lalu as $data) {
            $no_spjs[] = '\''.$data['no_spj'].'\'';
        }
        $no_spjs = implode(',', $no_spjs); 
        // var_dump(strlen($no_spjs));
        IF($model->status == 1){
            //jika masih draft munculkan semua spj
            $searchModel = new \app\modules\penatausahaan\models\TaSPJSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['tahun' => $Tahun]);
            $dataProvider->query->andWhere(['kd_sah' => 2]);
            $dataProvider->query->andWhere('tgl_spj <= \''.$model->tgl_sp3b.'\'');
            IF(strlen($no_spjs) > 0) $dataProvider->query->andWhere("no_spj NOT IN ( $no_spjs )");
        }ELSE{
            $searchModel = new \app\modules\pelaporan\models\TaSP3BRincSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['tahun' => $Tahun]);
            $dataProvider->query->andWhere(['no_sp3b' => $no_sp3b]);            
        }

        IF(isset($_POST) AND $_POST <> NULL){
            // var_dump($_POST); 
            foreach ($_POST['selection'] as $value) {
                //convert to array, array key tahun, no_bukti, tgl_bukti
                $data = \yii\helpers\Json::decode($value);
                $bukti = \app\models\TaSPJRinc::findOne(['tahun' => $data['tahun'], 'no_bukti' => $data['no_bukti'] ]);
                $bukti->no_spj = $no_spj;

                $bukti->save();

                // print_r($data);
                // echo '-';
                // print_r($data['tahun']);
                // echo '-';
                // print_r($data['no_bukti']);
                // echo '</br>';
            }
        }

        return $this->render('spjbukti', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'Tahun' => $Tahun,
        ]);
    }  

    public function actionStatus($kd, $tahun, $no_sp3b)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        $model = $this->findModel($tahun, $no_sp3b);
        switch ($kd) {
            case 1:
                $model->status = 2;
                $model->save();
                Yii::$app->getSession()->setFlash('Success',  'SP3B Ini sudah difinalkan.');
                return $this->redirect(Yii::$app->request->referrer);
                break;
            case 2:
                $model->status = 1;
                $model->save();
                Yii::$app->getSession()->setFlash('Success',  'SP3B Ini sudah di draft ulang.');
                return $this->redirect(Yii::$app->request->referrer);
                break;            
            default:
                # code...
                break;
        }

    }     

    public function actionAssign($kd, $tahun, $no_sp3b, $no_spj)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        switch ($kd) {
            case 1: //assign
                $sp3b = \app\models\TaSP3B::findOne(['tahun' => $tahun, 'no_sp3b' => $no_sp3b]);
                $spj = \app\models\TaSPJ::findOne(['tahun' => $tahun, 'no_spj' => $no_spj]);
                $sp3brinc = new \app\models\TaSP3BRinc();
                $sp3brinc->tahun = $tahun;
                $sp3brinc->no_sp3b = $no_sp3b;
                $sp3brinc->sekolah_id = $spj->sekolah_id;
                $sp3brinc->no_spj = $no_spj;
                // var_dump($sp3brinc->validate());
                IF($sp3brinc->validate() == true){
                    IF($sp3brinc->save()){
                        Yii::$app->getSession()->setFlash('success',  'SPJ Berhasil Ditambahkan.');
                        return $this->redirect(Yii::$app->request->referrer);                        
                    }ELSE{
                        Yii::$app->getSession()->setFlash('warning',  'SPJ gagal ditambahkan.');
                        return $this->redirect(Yii::$app->request->referrer);                                                
                    }
                }
                break;
            case 0: //hapus 
                $spj = \app\models\TaSPJ::findOne(['tahun' => $tahun, 'no_spj' => $no_spj]);
                $sp3brinc = \app\models\TaSP3BRinc::findOne(['tahun' => $tahun, 'no_sp3b' => $no_sp3b, 'no_spj' => $no_spj, 'sekolah_id' => $spj->sekolah_id]);
                IF($sp3brinc->delete()){
                    Yii::$app->getSession()->setFlash('success',  'SPJ Berhasil Dihapuskan dari lampiran.');
                    return $this->redirect(Yii::$app->request->referrer);                        
                }ELSE{
                    Yii::$app->getSession()->setFlash('warning',  'SPJ gagal dikeluarkan dari lampiran.');
                    return $this->redirect(Yii::$app->request->referrer);                                                
                }
                break;
            default:
                // code goes here
                break;
        }
    }        

    public function actionCreate()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $model = new TaSP3B();
        $model->tahun = $Tahun;
        $model->status = 1;

        if ($model->load(Yii::$app->request->post())) {
            IF($model->save()){
                echo 1;
            }ELSE{
                echo 0;
            }
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($tahun, $no_sp3b)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $model = $this->findModel($tahun, $no_sp3b);
        IF($model->status <> 1){
            Yii::$app->getSession()->setFlash('warning',  'SP3B ini sudah diproses SP2B, tidak dapat diubah atau dihapus.');
            return $this->redirect(Yii::$app->request->referrer);
        }        

        if ($model->load(Yii::$app->request->post())) {
            \app\models\TaSP3BRinc::updateAll(['no_sp3b' => $model->no_sp3b], 'no_sp3b = \''.$no_sp3b.'\'');
            IF($model->save()){
                echo 1;
            }ELSE{
                echo 0;
            }
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($tahun, $no_sp3b)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        $model = $this->findModel($tahun, $no_sp3b);
        IF($model->status == 1){
            $model->delete();
        }ELSE{
            Yii::$app->getSession()->setFlash('warning',  'SP3B ini sudah diproses SP2B, tidak dapat diubah atau dihapus.');
        }
        

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionPrintspj($tahun, $no_spj)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $model = \app\models\TaSPJ::findOne(['tahun' => $tahun, 'no_spj' => $no_spj]);
        $references = \app\models\TaTh::findOne(['tahun' => $tahun]);
        //query untuk halaman SPJ
        $sekolah = $model->sekolah_id;
        $tgl_spj = $model->tgl_spj;
        $spjdata = \Yii::$app->db->createCommand("
            SELECT a.tahun, a.sekolah_id, a.kd_program, c.uraian_program, a.kd_sub_program, d.uraian_sub_program, a.kd_kegiatan, e.uraian_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, h.Nm_Rek_5, a.anggaran,
            IFNULL(f.nilai,0) AS spjlalu, IFNULL(g.nilai,0) AS spjini, (IFNULL(f.nilai,0) + IFNULL(g.nilai,0)) AS sdspjini, (a.anggaran - (IFNULL(f.nilai,0) + IFNULL(g.nilai,0))) AS sisa_anggaran
            FROM
            (
                SELECT 
                a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.anggaran, b.nilai
                FROM
                (
                    SELECT
                    a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, SUM(a.total) AS anggaran
                    FROM
                    ta_rkas_history a
                    WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = $tahun AND sekolah_id = $sekolah AND tgl_peraturan <= '$tgl_spj')
                    GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5
                )a INNER JOIN
                (
                    SELECT a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, SUM(a.nilai) AS nilai
                    FROM ta_spj_rinc a WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.tgl_bukti <= '$tgl_spj' AND a.no_spj IS NOT NULL
                    GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5
                )b ON a.tahun = b.tahun AND a.sekolah_id = b.sekolah_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan
                    AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
            ) a 
            -- Untuk saldo s/d spj lalu
            LEFT JOIN
            (
                SELECT
                a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                SUM(a.nilai) AS nilai
                FROM
                ta_spj_rinc AS a
                LEFT JOIN
                (
                    SELECT 
                    a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                    FROM ta_rkas_history a 
                    WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = $tahun AND sekolah_id = 1 AND tgl_peraturan <= '$tgl_spj')
                    GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                ) b ON a.tahun = b.tahun AND a.sekolah_id = b.sekolah_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.tgl_bukti <= '$tgl_spj' AND a.no_spj IS NOT NULL AND a.no_spj <> '$no_spj'
                GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5
            ) f ON a.tahun = f.tahun AND a.sekolah_id = f.sekolah_id AND a.kd_program = f.kd_program AND a.kd_sub_program = f.kd_sub_program AND a.kd_kegiatan = f.kd_kegiatan AND a.Kd_Rek_1 = f.Kd_Rek_1 AND a.Kd_rek_2 = f.Kd_Rek_2 AND a.Kd_Rek_3 = f.Kd_Rek_3 AND a.Kd_Rek_4 = f.Kd_Rek_4 AND a.Kd_Rek_5 = f.Kd_Rek_5
            -- Untuk saldo s/d spj saat ini
            LEFT JOIN
            (
                SELECT
                a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                SUM(a.nilai) AS nilai
                FROM
                ta_spj_rinc AS a
                LEFT JOIN
                (
                    SELECT 
                    a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                    FROM ta_rkas_history a 
                    WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = $tahun AND sekolah_id = 1 AND tgl_peraturan <= '$tgl_spj')
                    GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                ) b ON a.tahun = b.tahun AND a.sekolah_id = b.sekolah_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                WHERE a.tahun = $tahun AND a.sekolah_id = $sekolah AND a.tgl_bukti <= '$tgl_spj' AND a.no_spj = '$no_spj'
                GROUP BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5
            ) g ON a.tahun = g.tahun AND a.sekolah_id = g.sekolah_id AND a.kd_program = g.kd_program AND a.kd_sub_program = g.kd_sub_program AND a.kd_kegiatan = g.kd_kegiatan AND a.Kd_Rek_1 = g.Kd_Rek_1 AND a.Kd_rek_2 = g.Kd_Rek_2 AND a.Kd_Rek_3 = g.Kd_Rek_3 AND a.Kd_Rek_4 = g.Kd_Rek_4 AND a.Kd_Rek_5 = g.Kd_Rek_5
            LEFT JOIN ref_program_sekolah c ON a.kd_program = c.kd_program
            LEFT JOIN ref_sub_program_sekolah d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
            LEFT JOIN ref_kegiatan_sekolah e ON a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan
            LEFT JOIN ref_rek_5 h ON a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 = h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5
            ORDER BY a.tahun, a.sekolah_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5 ASC
            ");
        $data = $spjdata->queryAll();        
        //find all bukti
        $bukti = $this->findBuktispj($tahun, $no_spj);

        return $this->render('printspj', [
            'model' => $model,
            'data' => $data,
            'bukti' => $bukti,
            'ref' => $references,
        ]);
    }      

    protected function findModel($tahun, $no_sp3b)
    {
        if (($model = TaSP3B::findOne(['tahun' => $tahun, 'no_sp3b' => $no_sp3b])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findBukti($tahun, $no_sp3b)
    {
        if (($model = \app\models\TaSP3BRinc::find()->where(['tahun' => $tahun, 'no_sp3b' => $no_sp3b])
        ->orderBy('sekolah_id, no_spj')
        ->all() ) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }   

    protected function findBuktispj($tahun, $no_spj)
    {
        if (($model = \app\models\TaSPJRinc::find()->where(['tahun' => $tahun, 'no_spj' => $no_spj])
        ->orderBy('tgl_bukti, no_bukti, kd_program, kd_sub_program, kd_kegiatan, Kd_Rek_1, Kd_Rek_2, Kd_Rek_3, Kd_Rek_4, Kd_Rek_5')
        ->all() ) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }   


    protected function cekakses(){

        IF(Yii::$app->user->identity){
            $akses = \app\models\RefUserMenu::find()->where(['kd_user' => Yii::$app->user->identity->kd_user, 'menu' => 604])->one();
            IF($akses){
                return true;
            }else{
                return false;
            }
        }ELSE{
            return false;
        }
    }  

}
