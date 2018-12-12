<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Excel extends \think\Model
{
	protected function column_str($key)
	{
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ');
		return $array[$key];
	}

	protected function column($key, $columnnum = 1)
	{
		return $this->column_str($key) . $columnnum;
	}

	/**
     * 导出Excel
     * @param type $list
     * @param type $params
     */
	public function export($list, $params = array())
	{
		if (PHP_SAPI == 'cli') {
			exit('This example should only be run from a Web Browser');
		}

		vendor('phpoffice.phpexcel.Classes.PHPExcel');
        $objPHPExcel = new \PHPExcel();  
        $objSheet = $objPHPExcel->getActiveSheet();  
        $objSheet ->setTitle($params['title']); 

        $rownum = 1;
		foreach ($params['columns'] as $key => $column) {
			$objSheet->setCellValue($this->column($key, $rownum), $column['title']);

			if (!empty($column['width'])) {
				$objSheet->getColumnDimension($this->column_str($key))->setWidth($column['width']);
			}
		}

		++$rownum;
		$len = count($params['columns']);

		foreach ($list as $row) {
			$i = 0;

			while ($i < $len) {
				$value = (isset($row[$params['columns'][$i]['field']]) ? $row[$params['columns'][$i]['field']] : '');
				$objSheet->setCellValue($this->column($i, $rownum), $value);
				++$i;
			}

			++$rownum;
		}

		$filename = urlencode($params['title'] . '-' . date('Y-m-d H:i', time()));

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');//生成一个Excel2007文件  
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存
		$objWriter->save("php://output");
		exit();
	}

	public function import($excefile) 
	{
		vendor('phpoffice.phpexcel.Classes.PHPExcel');
		$path = ROOT_PATH . '/public/static/tmp/';
		if (!(is_dir($path))) 
		{
			load()->func('file');
			mkdirs($path, '0777');
		}
		$filename = $_FILES[$excefile]['name'];
		$tmpname = $_FILES[$excefile]['tmp_name'];
		if (empty($tmpname)) 
		{
			message('请选择要上传的Excel文件!', '', 'error');
		}
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if (($ext != 'xlsx') && ($ext != 'xls')) 
		{
			message('请上传 xls 或 xlsx 格式的Excel文件!', '', 'error');
		}
		$file = time() . $_W['uniacid'] . '.' . $ext;
		$uploadfile = $path . $file;
		$result = move_uploaded_file($tmpname, $uploadfile);
		if (!($result)) 
		{
			message('上传Excel 文件失败, 请重新上传!', '', 'error');
		}
		$reader = PHPExcel_IOFactory::createReader(($ext == 'xls' ? 'Excel5' : 'Excel2007'));
		$excel = $reader->load($uploadfile);
		$sheet = $excel->getActiveSheet();
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnCount = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$values = array();
		$row = 1;
		while ($row <= $highestRow) 
		{
			$rowValue = array();
			$col = 0;
			while ($col < $highestColumnCount) 
			{
				$rowValue[] = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
				++$col;
			}
			$values[] = $rowValue;
			++$row;
		}
		return $values;
	}
	
}