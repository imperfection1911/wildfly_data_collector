<?php
require_once('./excel/Classes/PHPExcel.php');
require_once('config.php');
class excelReport
{
	public function __construct()
	{
		$this->excel = new PHPExcel();
 		$this->excel->getProperties()->setCreator("Антон Аксенов")
 							   ->setLastModifiedBy("Антон Аксенов")
 							   ->setTitle("Паспорт ESB")
 							   ->setSubject("Паспорт ESB")
 							   ->setDescription("Паспорт ESB");
 		//шрифт
 		$this->excel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
 		$this->sheetIndex =0;
 		$this->moduleStart=2;
 		$this->datasourceStart=2;
 		$this->jmsStart=2;


	}
//прописать в лист заголовки. на вход массив вида
/*	array('Сервер'=>array('A','B'),'Порт'=>array('C'),'Домен'=>array('D','E'))
*/	
	public function createHeader($headers)
	{
		foreach($headers as $header=>$cells)
		{
			$cellsLastIndex=count($cells)-1;
			$this->sheet->setCellValue($cells[0].'1',$header);
			//мержим ячейки, если заголовок занимает больше одной
			if(count($cells)>1)
			{
				$this->sheet->mergeCells($cells[0].'1:'.$cells[$cellsLastIndex].'1');
			}
			//выбираем метод заливки. Их много описано в этой статье https://habrahabr.ru/post/245233/
 			$this->sheet->getStyle($cells[0].'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
 			//ставим цвет
 			$this->sheet->getStyle($cells[0].'1')->getFill()->getStartColor()->setRGB('97FFFF');
 			//выравниваем текст в ячейках
 			$this->sheet->getStyle($cells[0].'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
 			$this->drawAllBorder($cells,'1');
		}
	}


	public function createSheet($name)
	{
		if($this->sheetIndex > 0)
		{
			$this->excel->createSheet();
		}
		$this->excel->setActiveSheetIndex($this->sheetIndex);
		$this->sheet=$this->excel->getActiveSheet();
		$this->sheet->setTitle($name);
		$this->sheetIndex++;
	}

	// отрисовать рамку вокруг ячеек
	public function drawAllBorder($cells,$number)
	{
		//настройки рамки
		$borderSettings = array('bottom'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN,'color'=>array('rgb'=> '000000')),
 				'top'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN,'color'=>array('rgb'=> '000000')),
 				'right'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN,'color'=>array('rgb'=> '000000')),
 				'left'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN,'color'=>array('rgb'=> '000000')));
		foreach($cells as $cell)
		{	
			$this->sheet->getStyle($cell.$number)->getBorders()->applyFromArray($borderSettings);
		}
	}



//сохранить отчет, закончить работу.
	public function saveReport()
	{
		global $outFile;
		$this->writer = new PHPExcel_Writer_Excel5($this->excel);
 		$this->writer->save($outFile);
	}


//выравнивание. верх по вертикали, центр по горизонтали
	public function aligment($cell)
	{
		$this->sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
 		$this->sheet->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	}




public function createReport($report,$moduleSheet,$datasourceSheet,$jmsSheet)
{
	foreach($report as $server=>$data)
	{
		//заполнение ячейки с сервером на кажжом листе
		$this->excel->setActiveSheetIndex($moduleSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	$this->sheet->setCellValue('A'.$this->moduleStart,$server);
	 	$this->aligment('A'.$this->moduleStart);
	 	$this->excel->setActiveSheetIndex($datasourceSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	$this->sheet->setCellValue('A'.$this->datasourceStart,$server);
	 	$this->aligment('A'.$this->datasourceStart);
	 	$this->excel->setActiveSheetIndex($jmsSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	$this->sheet->setCellValue('A'.$this->jmsStart,$server);
	 	$this->aligment('A'.$this->jmsStart);
	 	$moduleSaved = $this->moduleStart;
	 	$datasourceSaved = $this->datasourceStart;
	 	$jmsSaved = $this->jmsStart;
	 	//вносим модули
	 	$this->excel->setActiveSheetIndex($moduleSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	#print_r($data);
	 	$modulesCount = count($data['modules']);
	 	foreach($data['modules'] as $module=>$moduleProperties)
	 	{
	 		$this->sheet->setCellValue('C'.$this->moduleStart,$module);
	 		$this->aligment('C'.$this->moduleStart);
	 		$cellColor = '98FB98';
	 		if(!$moduleProperties['enabled'])
	 		{
	 			$cellColor='CD5C5C';

	 		}
	 		$this->sheet->getStyle('C'.$this->moduleStart)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
 			$this->sheet->getStyle('C'.$this->moduleStart)->getFill()->getStartColor()->setRGB($cellColor);
 			$this->sheet->mergeCells('C'.$this->moduleStart.':F'.$this->moduleStart);
 			$this->drawAllBorder(array('C','D','E','F'),$this->moduleStart);
 			$this->moduleStart++;

	 	}
	 	//мержим сервер по кол-ву модулей
	 	$moduleMerge = $this->moduleStart-1;
	 	$this->sheet->mergeCells('A'.$moduleSaved.':B'.$moduleMerge);
	 	foreach(range($moduleSaved,$moduleMerge) as $i)
	 	{
	 		$this->drawAllBorder(array('A','B'),$i);
	 	}
	 	//вносим датасурсы
	 	$this->excel->setActiveSheetIndex($datasourceSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	$datasourceCount = count($data['datasources']);
	 	foreach($data['datasources'] as $db=>$datasource)
	 	{
	 		//имя базы
	 		$this->sheet->setCellValue('C'.$this->datasourceStart,$db);
	 		$this->aligment('C'.$this->datasourceStart);
	 		$this->sheet->mergeCells('C'.$this->datasourceStart.':D'.$this->datasourceStart);
	 		$this->drawAllBorder(array('C','D'),$this->datasourceStart);
	 		//урл
	 		$this->sheet->setCellValue('E'.$this->datasourceStart,$datasource['url']);
	 		$this->aligment('E'.$this->datasourceStart);
	 		$this->sheet->mergeCells('E'.$this->datasourceStart.':H'.$this->datasourceStart);
	 		$this->drawAllBorder(array('E','F','G','H'),$this->datasourceStart);
	 		//драйвер
	 		$this->sheet->setCellValue('I'.$this->datasourceStart,$datasource['driver']);
	 		$this->aligment('I'.$this->datasourceStart);
	 		$this->sheet->mergeCells('I'.$this->datasourceStart.':J'.$this->datasourceStart);
	 		$this->drawAllBorder(array('I','J'),$this->datasourceStart);
	 		//логин
	 		$this->sheet->setCellValue('K'.$this->datasourceStart,$datasource['login']);
	 		$this->aligment('K'.$this->datasourceStart);
	 		$this->sheet->mergeCells('K'.$this->datasourceStart.':M'.$this->datasourceStart);
	 		$this->drawAllBorder(array('K','L','M'),$this->datasourceStart);
	 		//пароль
	 		$this->sheet->setCellValue('N'.$this->datasourceStart,$datasource['password']);
	 		$this->aligment('N'.$this->datasourceStart);
	 		$this->sheet->mergeCells('N'.$this->datasourceStart.':P'.$this->datasourceStart);
	 		$this->drawAllBorder(array('N','O','P'),$this->datasourceStart);
	 		$cellColor = '98FB98';
	 		if(!$datasource['enabled'])
	 		{
	 			$cellColor='CD5C5C';
	 		}
	 		$cells = array('C','D','E','F','G','H','I','G','K','L','M','N','O','P');
	 		for($i=0;$i<count($cells);$i++)
	 		{
	 			$this->sheet->getStyle($cells[$i].$this->datasourceStart)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
 				$this->sheet->getStyle($cells[$i].$this->datasourceStart)->getFill()->getStartColor()->setRGB($cellColor);
	 		}
	 		$this->datasourceStart++;

	 	}
	 	//мержим сервер.
	 	$datasourceMerge = $this->datasourceStart-1;
	 	$this->sheet->mergeCells('A'.$datasourceSaved.':B'.$datasourceMerge);
	 	foreach(range($datasourceSaved,$datasourceMerge) as $i)
	 	{
	 		$this->drawAllBorder(array('A','B'),$i);
	 	}
	 	//вносим jms очереди
	 	$this->excel->setActiveSheetIndex($jmsSheet);
	 	$this->sheet=$this->excel->getActiveSheet();
	 	$jmsCount = count($data['jms']);
	 	foreach($data['jms'] as $queue=>$properties)
	 	{
	 		$jsaved = $this->jmsStart;
	 		$this->sheet->setCellValue('C'.$this->jmsStart,$queue);
	 		$this->aligment('C'.$this->jmsStart);
	 		foreach($properties['jndiName'] as $jndi)
	 		{
	 			$this->sheet->setCellValue('F'.$this->jmsStart,$jndi);
	 			$this->aligment('F'.$this->jmsStart);
	 			$this->sheet->mergeCells('F'.$this->jmsStart.':H'.$this->jmsStart);
	 			$this->drawAllBorder(array('F','G','H'),$this->jmsStart);
	 			$this->jmsStart++;
	 		}
	 		$mergeJMSname = $this->jmsStart-1;
	 		$this->sheet->mergeCells('C'.$jsaved.':E'.$mergeJMSname);
	 		foreach(range($jsaved,$mergeJMSname) as $i)
	 		{
	 			$this->drawAllBorder(array('C','D','E'),$i);
	 		}
	 	}
	 	$jmsMerge = $this->jmsStart-1;
	 	$this->sheet->mergeCells('A'.$jmsSaved.':B'.$jmsMerge);
	 	foreach(range($jmsSaved,$jmsMerge) as $i)
	 	{
	 		$this->drawAllBorder(array('A','B'),$i);
	 	}




	 	


	}
}




}