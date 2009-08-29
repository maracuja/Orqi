<?
	require_once "_classes/_inf/_core/BaseConfig.php";
	require_once "_classes/_inf/_core/Object.php";
	require_once "_classes/_inf/_core/DAO.php";
	include_once('_lib/symfony/yaml/sfYaml.class.php');
	require_once "config.php";

	$config = Config::GetInstance();
	$array = sfYaml::load('_config/schema.yml');

	echo "<pre>";
	
	// == DOMAIN FILES ======================================================================= //
	
	foreach ($array['data'] as $object)
	{
		$fp = fopen($config->base['domain'] . "/Base" . $object['_attributes']['phpName'] . ".php", 'w');
		fwrite($fp, "<?\n");
		fwrite($fp, "\tclass Base" . $object['_attributes']['phpName'] . " extends Object\n\t{\n");

		// class attributes
		$constructor_params_separator = "";
		$constructor_params_text = "";
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && empty($attributes['calculated']))
			{
				if ($attributes['type'] == "many")
				{
					fwrite($fp, "\t\tpublic \$" . $name . " = array();\n");
					fwrite($fp, "\t\tpublic \$count_" . $name . ";\n");
				}
				else
				{
					fwrite($fp, "\t\tpublic $" . $name . ";\n");
					$constructor_params_text .= $constructor_params_separator . "\$" . $name . "=''";
					$constructor_params_separator = ", ";
				}
			}
		}
		fwrite($fp, "\n");
		
		// write the sub-class's constructor
		fwrite($fp, "\t\tfunction __construct($constructor_params_text)\n\t\t{\n");
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many") fwrite($fp, "\t\t\t\$this->" . $name . " = \$" . $name . ";\n");
		}
		fwrite($fp, "\t\t}\n\n");
		
		// class getter members
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $name != "id" && $attributes['type'] != "many")
			{
				fwrite($fp, "\t\tfunction Get" . ucwords($name) . "(" . (($attributes['type'] == 'timestamp') ? "\$format=''" : "") . ")\n\t\t{\n");
				fwrite($fp, "\t\t\tif (\$this->isGhost()) \$this->PopulateMe();\n");
				
				if (!empty($attributes['calculated']))
				{
					fwrite($fp, "\t\t\tif (empty(\$this->" . $name . ")) \$this->" . $name . " = " . $attributes['calculated'] . ";\n");
				}
				if (!empty($attributes['linkedObject']))
				{
					fwrite($fp, "\t\t\tif (!is_a(\$this->" . $name . ", '" . $attributes['linkedObject'] . "')) \$this->" . $name . " = new " . $attributes['linkedObject'] . "(\$this->" . $name . ");\n");
				}
				if ($attributes['type'] == 'timestamp')
				{
					fwrite($fp, "\t\t\treturn (empty(\$format)) ? \$this->" . $name . " : date(\$format, \$this->" . $name . ");\n");
				}
				else fwrite($fp, "\t\t\treturn \$this->" . $name . ";\n");
				fwrite($fp, "\t\t}\n");
			}
		}
		fwrite($fp, "\n");
		
		// class setter members
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $name != "id" && $attributes['type'] != "many")
			{
				if ($attributes['type'] == 'timestamp')
				{
					fwrite($fp, "\t\tfunction Set" . ucwords($name) . "($" . $name . "='') { \$this->" . $name . " = (ereg('^([0-9])+$', $" . $name . ")) ? $" . $name . " : oDate::String2Time($" . $name . "); }\n");
				}
				else fwrite($fp, "\t\tfunction Set" . ucwords($name) . "($" . $name . "='') { \$this->" . $name . " = \$" . $name . "; }\n");
			}
		}
		
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $name != "id" && $attributes['type'] == "many")
			{
		fwrite($fp, "
		
		function has" . ucwords($name) . "() { return (\$this->Count" . ucwords($name) . "() > 0) ? true : false; }
			
		function Count" . ucwords($name) . "()
		{
			if (\$this->isGhost()) \$this->PopulateMe();
			if (empty(\$this->count_" . strtolower($name) . "))
			{
				\$" . strtolower($name) . "_mapper = new " . ucwords($name) . "();
				\$this->count_" . strtolower($name) . " = \$" . strtolower($name) . "_mapper->GetTotalRowsBy" . $object['_attributes']['phpName'] . "(\$this);
			}
			return \$this->count_" . strtolower($name) . ";
		}
		
		function Get" . ucwords($name) . "(\$page='')
		{
			if (\$this->isGhost()) \$this->PopulateMe();
			if (empty(\$this->" . strtolower($name) . "))
			{
				\$" . strtolower($name) . "_mapper = new " . ucwords($name) . "();
				\$this->" . strtolower($name) . " = \$" . strtolower($name) . "_mapper->FindBy" . $object['_attributes']['phpName'] . "(\$this, \$page);
			}
			return \$this->" . strtolower($name) . ";
		}\n");
			}
		}
		
		fwrite($fp, "\t}\n");
		fclose($fp);
		@chmod($config->base['domain'] . "/Base" . $object['_attributes']['phpName'] . ".php", 0777);
		
		if (!file_exists($config->classes['domain'] . "/" . $object['_attributes']['phpName'] . ".php"))
		{
			$fp = fopen($config->classes['domain'] . "/" . $object['_attributes']['phpName'] . ".php", 'w');
			fwrite($fp, "<?\n");
			fwrite($fp, "\tclass " . $object['_attributes']['phpName'] . " extends Base" . $object['_attributes']['phpName'] . "\n\t{ }\n");
			fclose($fp);
			chmod($config->classes['domain'] . "/" . $object['_attributes']['phpName'] . ".php", 0777);
		}
	}
	
	// == MAPPER FILES ======================================================================= //
	
	foreach ($array['data'] as $object)
	{
		$mapper_name = Object::GetPlural($object['_attributes']['phpName']);
		if (empty($object['_orderby'])) $order_by = "id";
		else
		{
			$separator = "";
			$order_by = "";
			foreach ($object['_orderby'] as $column)
			{
				$order_by .= $separator . $column;
				$separator = ", ";
			}
		}
		$order_by_clause = " order by $order_by ";
		$label = (empty($object['_listcolumn'])) ? "name" : $object['']['labels'];
		if (is_array($label)) $label = $label[0];
		
		$fp = fopen($config->base['mapper'] . "/Base" . ucwords($mapper_name) . ".php", 'w');
		fwrite($fp, "<?\n");
		fwrite($fp, "\tclass Base" . ucwords($mapper_name) . " extends " . (($object['_attributes']['authenticator'] == true) ? "Auth" : "") . "Mapper
	{
		function GetSelectBoxData(\$filter='')
		{
			\$data = array();
			\$filter = (empty(\$filter)) ? \"\" : \" where \$filter \";
			\$sql_string = \"select id as value, `" . $label . "` as text from " . strtolower($mapper_name) . " \$filter " . $order_by_clause . "\";
			\$recordset = \$this->dao->ExecuteQuery(\$sql_string);
			if (\$recordset) while (\$record = mysql_fetch_assoc(\$recordset)) \$data[] = \$record;
			return \$data;
		}
		
		function GetTotalRows()
		{
			\$sql_string = \"select count(*) as total_rows from `" . strtolower($mapper_name) . "`\";
			return \$this->dao->GetValue(\$sql_string, 'total_rows');
		}
		
		function FindAll(\$page='')
		{
			\$sql_string = \"select * from `" . strtolower($mapper_name) . "` " . $order_by_clause . "\";
			\$sql_string .= \$this->GetPageClause(\$page);
			return \$this->dao->GetObjectArray(\$sql_string, new " . $object['_attributes']['phpName'] . "());
		}
		");
		
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $name != "id" && $attributes['searchable'] == "true")
			{
				$quotes = ($attributes['type'] == "varchar" || $attributes['type'] == "text") ? "'" : "";

				fwrite($fp, "
		function FindBy" . ucwords($name) . "(\$" . strtolower($name) . "='')
		{
			if (empty(\$" . strtolower($name) . ")) return false;
			\$sql_string = \"select * from `" . strtolower($mapper_name) . "` where " . strtolower($name) . "=" . $quotes . "\" . \$" . strtolower($name) . " . \"" . $quotes . " $order_by_clause \";
			return \$this->dao->GetObject(\$sql_string, new " . $object['_attributes']['phpName'] . "());
		}
		");
			}
			
			if ($name[0] != "_" && $name != "id" && !empty($attributes['linkedObject']) && $attributes['type'] != "many")
			{
				fwrite($fp, "
		function GetTotalRowsBy" . ucwords($name) . "(\$" . strtolower($attributes['linkedObject']) . "='')
		{
			if (!is_a(\$" . strtolower($attributes['linkedObject']) . ", '" . ucwords($attributes['linkedObject']) . "')) return 0;
			\$sql_string = \"select count(*) as total_rows from `" . strtolower($mapper_name) . "` where " . strtolower($attributes['linkedObject']) . "=\" . \$" . strtolower($attributes['linkedObject']) . "->GetId();
			return \$this->dao->GetValue(\$sql_string, 'total_rows');
		}
		
		function FindBy" . ucwords($name) . "(\$" . strtolower($attributes['linkedObject']) . "='', \$page='')
		{
			if (!is_a(\$" . strtolower($attributes['linkedObject']) . ", '" . ucwords($attributes['linkedObject']) . "')) return false;
			\$sql_string = \"select * from `" . strtolower($mapper_name) . "` where " . strtolower($attributes['linkedObject']) . "=\" . \$" . strtolower($attributes['linkedObject']) . "->GetId() . \"$order_by_clause\";
			\$sql_string .= \$this->GetPageClause(\$page);
			return \$this->dao->GetObjectArray(\$sql_string, new " . $object['_attributes']['phpName'] . "());
		}
		");
			}
		}
		
		if (!empty($object['_searchon']))
		{
			$separator = "";
			$search_on = "";
			foreach ($object['_searchon'] as $column)
			{
				$search_on .= $separator . "'" . $column . "'";
				$separator = ", ";
			}
				
		fwrite($fp, "
		function GetTotalRowsBySearch(\$query='')
		{
			if (empty(\$query)) return 0;
			\$sql_string = \"
				select		count(*) as total_rows
				from		users
				where		\" . \$this->GetQueryClause(\$query, array(" . $search_on . ")) . \"
			\";
			return \$this->dao->GetValue(\$sql_string, 'total_rows');
		}
		
		function FindBySearch(\$query='', \$page='')
		{
			\$data = array();
			if (empty(\$query)) return \$data;
			\$sql_string = \"
				select		*
				from		users
				where		\" . \$this->GetQueryClause(\$query, array(" . $search_on . ")) . \"
				" . $order_by_clause . "
			\";
			\$sql_string .= \$this->GetPageClause(\$page);
			return \$this->dao->GetObject(\$sql_string, new User());
		}
		");
		}
		
		fwrite($fp, "
		function Save(&\$object)
		{
			\$action_text = (\$object->isNew()) ? \"insert into \" : \"update \";
			if (!\$object->isNew()) \$condition = \"where id=\" . \$object->GetId();
			\$sql_string = \"
				\$action_text	`" . strtolower($mapper_name) . "`
				set");

		$separator = "";
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $name != "id" && $attributes['type'] != "many")
			{
				$linkedObject = (!empty($attributes['linkedObject'])) ? "->GetId()" : "";
				$quotes = ($attributes['type'] == 'varchar' || $attributes['type'] == 'text') ? "'" : "";
				
				if ($attributes['required'])
				{
					fwrite($fp, $separator . "\t\t\t\t`" . $name . "`=" . $quotes . "\" . \$object->Get" . ucwords($name) . "()" . $linkedObject . " . \"" . $quotes);
				}
				else
				{
					fwrite($fp, $separator . "\t\t\t\t`" . $name . "`=\" . ((\$object->Get" . ucwords($name) . "()" . $linkedObject . " == '') ? \"NULL\" : \"" . $quotes . "\" . \$object->Get" . ucwords($name) . "()" . $linkedObject . " . \"" . $quotes . "\") . \"");
				}
				$separator = ", \n";
			}
		}		
		
		fwrite($fp, "
				\$condition
			\";

			if (\$this->dao->ExecuteQuery(\$sql_string))
			{
				if (\$object->isNew()) \$object->SetId(mysql_insert_id(\$this->dao->conn));
				return true;
			}
			else return false;
		}\n");
		fwrite($fp, "\t}\n");
		fclose($fp);
		@chmod($config->base['mapper'] . "/Base" . ucwords($mapper_name) . ".php", 0777);
		
		$mapper_name = Object::GetPlural($object['_attributes']['phpName']);
		if (!file_exists($config->classes['mapper'] . "/" . ucwords($mapper_name) . ".php"))
		{
			$label = (empty($object['_listcolumns'])) ? "name" : $object['_attributes']['labels'];
			if (is_array($label)) $label = $label[0];
			$fp = fopen($config->classes['mapper'] . "/" . ucwords($mapper_name) . ".php", 'w');
			fwrite($fp, "<?\n");
			fwrite($fp, "\tclass " . ucwords($mapper_name) . " extends Base" . ucwords($mapper_name) . " { }\n");
			fclose($fp);
			chmod($config->classes['mapper'] . "/" . ucwords($mapper_name) . ".php", 0777);
		}
	}
	
	// == VALIDATOR FILES ==================================================================== //
	
	foreach ($array['data'] as $object)
	{
		$fp = fopen($config->base['validator'] . "/Base" . $object['_attributes']['phpName'] . "Validator.php", 'w');
		fwrite($fp, "<?\n");
		fwrite($fp, "\tclass Base" . $object['_attributes']['phpName'] . "Validator extends Validator\n\t{\n");

		// class attributes
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
			{
				fwrite($fp, "\t\tpublic $" . $name . ";\n");
				if ($attributes['confirm'] == true) fwrite($fp, "\t\tpublic $" . $name . "confirm;\n");
			}
		}
		fwrite($fp, "\n");
		
		// class getter members
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
			{
				fwrite($fp, "\t\tfunction Get" . ucwords($name) . "() { return \$this->" . $name . "; } \n");
				if ($attributes['confirm'] == true) fwrite($fp, "\t\tfunction Get" . ucwords($name) . "confirm() { return \$this->" . $name . "confirm; } \n");
			}
		}
		fwrite($fp, "\n");
		
		// class setter members
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
			{
				fwrite($fp, "\t\tfunction Set" . ucwords($name) . "($" . $name . "='') { \$this->" . $name . " = \$" . $name . "; }\n");
				if ($attributes['confirm'] == true) fwrite($fp, "\t\tfunction Set" . ucwords($name) . "confirm($" . $name . "confirm='') { \$this->" . $name . "confirm = \$" . $name . "confirm; }\n");
			}
		}
		fwrite($fp, "\n");

		// populate function
		fwrite($fp, "\t\tfunction Populate(\$post='')\n\t\t{\n\t\t\tif (is_a(\$post, '" . $object['_attributes']['phpName'] . "'))\n\t\t\t{\n");
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
			{
				$linkedObject = (!empty($attributes['linkedObject'])) ? "->GetId()" : "";
				fwrite($fp, "\t\t\t\t\$this->" . $name . " = \$post->Get" . ucwords($name) . "(" . (($attributes['type'] == 'timestamp') ? "\$this->config->date_formats['default']" : "") . ")" . $linkedObject . ";\n");
			}
		}
		fwrite($fp, "\t\t\t}\n\t\t\telse\n\t\t\t{\n");
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
			{
				fwrite($fp, "\t\t\t\t\$this->" . $name . " = \$post['" . $name . "'];\n");
				if ($attributes['confirm'] == true) fwrite($fp, "\t\t\t\t\$this->" . $name . "confirm = \$post['" . $name . "confirm'];\n"); 
			}
		}
		fwrite($fp, "\t\t\t}\n\t\t}\n");
		fwrite($fp, "\t}\n");
		fclose($fp);
		@chmod($config->base['validator'] . "/Base" . $object['_attributes']['phpName'] . "Validator.php", 0777);
		
		if (!file_exists($config->classes['validator'] . "/" . $object['_attributes']['phpName'] . "Validator.php"))
		{
			$fp = fopen($config->classes['validator'] . "/" . $object['_attributes']['phpName'] . "Validator.php", 'w');
			fwrite($fp, "<?\n");
			fwrite($fp, "\tclass " . $object['_attributes']['phpName'] . "Validator extends Base" . $object['_attributes']['phpName'] . "Validator\n\t{\n");
			fwrite($fp, "\t\tfunction isValid()
		{
			\$this->errors = array();
			
			// TODO put validation rules here
			
			return empty(\$this->errors);
		}\n");
			fwrite($fp, "\t}\n");
			fclose($fp);
			chmod($config->classes['validator'] . "/" . $object['_attributes']['phpName'] . "Validator.php", 0777);
		}
	}
	
	// == CONTROLLER FILES =================================================================== //
	
	foreach ($array['data'] as $object)
	{
		$mapper_name = ucwords(Object::GetPlural($object['_attributes']['phpName']));
		$label = (empty($object['_listcolumns'])) ? "name" : $object['_listcolumns'];

		$fp = fopen($config->base['app'] . "/Base" . $object['_attributes']['phpName'] . "Controller.php", 'w');
		fwrite($fp, "<?\n");
		fwrite($fp, "\tclass Base" . $object['_attributes']['phpName'] . "Controller extends " . (($object['_attributes']['authenticator'] == true) ? "Auth" : "") . "Controller\n\t{");
		fwrite($fp, "
		function All()
		{
			\$this->breadcrumb[] = new Breadcrumblink(\$this->MakeLink(\$this->get['object'],  'all'), '" . $mapper_name . "');
			
			\$this->object = new " . $mapper_name . "();
			\$this->SetPagingVars();
			\$this->data = \$this->object->FindAll(\$this->page['current']);

			\$this->page_title = \"All " . $mapper_name . "\";
			\$this->headercomponents = array('PageHeader');
			\$this->footercomponents = array('PageFooter');
			
			\$this->listcolumns = array(\n");
		
			if (is_array($label))
			{
				$separator = "\t\t\t\t";
				foreach ($label as $column)
				{
					fwrite($fp, $separator . "new ListColumn('" . ucwords($column) . "', '" . $column . "', 200)");
					$separator = ",\n\t\t\t\t";	
				}
			}
			else
			{
				fwrite($fp, "\t\t\t\tnew ListColumn('" . ucwords($label) . "', '" . $label . "', 250)\n");
			}
			fwrite($fp, "\n\t\t\t);
			
			\$this->rowoptions = array(
				new RowOption('Edit', 'edit', 4),
				new RowOption('Delete', 'delete', 4)
			);
			
			\$this->LoadTemplate('DynamicList');
		}
		
		function Add() { \$this->Edit(); }
		function Edit()
		{
			\$this->breadcrumb[] = new Breadcrumblink(\$this->MakeLink(\$this->get['object'], 'all'), '" . $mapper_name . "');
			\$breadcrumb_label = (empty(\$this->get['id'])) ? 'New ' : 'Editing ';
			\$this->breadcrumb[] = new Breadcrumblink('', \$breadcrumb_label . ucwords(\$this->get['object']));
			
			\$this->mapper = new " . $mapper_name . "();
			\$this->form = new " . $object['_attributes']['phpName'] . "Validator();
			
			if (empty(\$this->post))
			{	
				if (!empty(\$this->get['id'])) \$this->form->Populate(\$this->mapper->Find(new " . $object['_attributes']['phpName'] . "(\$this->get['id'])));
			}
			else
			{
				\$this->form->Populate(\$this->post);
				if (\$this->form->isValid())
				{
					\$object = new " . $object['_attributes']['phpName'] . "();\n");
					
			foreach ($object as $name => $attributes)
			{
				if ($name[0] != "_" && $attributes['type'] != "many" && empty($attributes['calculated']))
				{
					if (empty($attributes['linkedObject'])) fwrite($fp, "\t\t\t\t\t\$object->Set" . ucwords($name) . "(\$this->form->Get" . ucwords($name) . "());\n");
					else fwrite($fp, "\t\t\t\t\t\$object->Set" . ucwords($name) . "(new " . $attributes['linkedObject'] . "(\$this->form->Get" . ucwords($name) . "()));\n");
				}
			}
					
			fwrite($fp, "
					if (\$this->mapper->Save(\$object))
					{
						\$this->session->AddMessage(new Message('success', \"The " . strtolower($object['_attributes']['phpName']) . " was saved successfully.\"));
						\$this->Redirect(\$this->MakeLink(\$this->get['object'], 'all', 'id=' . \$object->GetId()));
					}
					else
					{
						\$this->session->AddMessage(new Message('error', \"Couldn't save to the database\"));
					}
				}
			}
			
			\$this->LoadTemplate('Form" . $object['_attributes']['phpName'] . "');
		}\n");
		fwrite($fp, "\t}\n");
		fclose($fp);
		@chmod($config->base['app'] . "/Base" . $object['_attributes']['phpName'] . "Controller.php", 0777);
		
		if (!file_exists($config->classes['app'] . "/" . $object['_attributes']['phpName'] . "Controller.php"))
		{
			$fp = fopen($config->classes['app'] . "/" . $object['_attributes']['phpName'] . "Controller.php", 'w');
			fwrite($fp, "<?\n");
			fwrite($fp, "\tclass " . $object['_attributes']['phpName'] . "Controller extends Base" . $object['_attributes']['phpName'] . "Controller\n\t{");
			fwrite($fp, "\n\t}\n");
			fclose($fp);
			chmod($config->classes['app'] . "/" . $object['_attributes']['phpName'] . "Controller.php", 0777);
		}
	}
	
	// == FORM SCRIPTS ======================================================================= //
	
	if ($_GET['ui'])
	{
	foreach ($array['data'] as $object)
	{
		if (!file_exists($config->classes['ui'] . "/Form" . $object['_attributes']['phpName'] . ".php"))
		{
		$fp = fopen($config->classes['ui'] . "/Form" . $object['_attributes']['phpName'] . ".php", 'w');
		fwrite($fp, "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
	<? \$this->LoadComponent('HeadTag'); ?>
	
	<body>
		<? \$this->LoadComponent('PageHeader'); ?>
		
		<div id='main'>
			<h3>" . $object['_attributes']['phpName'] . " Edit Form</h3>
			<?=\$this->session->PrintMessages()?>
				
			<form method='post' action=''>
				<input type='hidden' name='id' value='<?=\$this->form->GetId()?>' />\n");
		
			foreach ($object as $name => $attributes)
			{
				if ($name[0] != "_" && $name != "id" && $attributes['type'] != "many" && empty($attributes['calculated']))
				{
					fwrite($fp, "				<div>
					<label for='" . $name . "'>" . ucwords($name) . "</label>\n");
					
					switch ($attributes['type'])
					{
						case "int":
							if (!empty($attributes['linkedObject']))
							{
								fwrite($fp, "\t\t\t\t\t<? \$control = new SelectBox(\$this->config, '" . ucwords(Object::GetPlural($attributes['linkedObject'])) . "', '" . strtolower($name) . "', '', '', \$this->form->Get" . ucwords($name) . "()); ?>\n");
								break;
							}

						case "timestamp":
						case "varchar":
							fwrite($fp, "\t\t\t\t\t<input type='text' name='" . $name . "' value=\"<?=\$this->form->Get" . ucwords($name) . "()?>\" id='" . $name . "' />\n");
							break;
							
						case "text":
							fwrite($fp, "\t\t\t\t\t<textarea name='" . $name . "' id='" . $name . "'><?=\$this->form->Get" . ucwords($name) . "()?></textarea>\n");
							break;
							
						case "tinyint":
							fwrite($fp, "\t\t\t\t\t<? \$control = new SelectBox('', '', '" . strtolower($name) . "', '', '', \$this->form->Get" . ucwords($name) . "()); ?>\n");
							break;
					}
					
					fwrite($fp, "\t\t\t\t\t<?=\$this->form->PrintError('" . $name . "')?>\n");
					fwrite($fp, "\t\t\t\t</div>\n");
					
					if ($attributes['confirm'] == true)
					{
						fwrite($fp, "				<div>
					<label for='" . $name . "confirm'>" . ucwords($name) . " Confirm</label>\n");
						
						switch ($attributes['type'])
						{
							case "int":
								if (!empty($attributes['linkedObject']))
								{
									fwrite($fp, "\t\t\t\t\t<? \$control = new SelectBox(\$this->config, '" . ucwords(Object::GetPlural($attributes['linkedObject'])) . "', '" . strtolower($name) . "confirm', '', '', \$this->form->Get" . ucwords($name) . "confirm()); ?>\n");
									break;
								}
	
							case "timestamp":
							case "varchar":
								fwrite($fp, "\t\t\t\t\t<input type='text' name='" . $name . "confirm' value=\"<?=\$this->form->Get" . ucwords($name) . "confirm()?>\" id='" . $name . "confirm' />\n");
								break;
								
							case "text":
								fwrite($fp, "\t\t\t\t\t<textarea name='" . $name . "confirm' id='" . $name . "confirm'><?=\$this->form->Get" . ucwords($name) . "confirm()?></textarea>\n");
								break;
								
							case "tinyint":
								fwrite($fp, "\t\t\t\t\t<? \$control = new SelectBox('', '', '" . strtolower($name) . "confirm', '', '', \$this->form->Get" . ucwords($name) . "confirm()); ?>\n");
								break;
						}
						
						fwrite($fp, "\t\t\t\t\t<?=\$this->form->PrintError('" . $name . "confirm')?>\n");
						fwrite($fp, "\t\t\t\t</div>\n");
					}
				}
			}
			fwrite($fp, "\t\t\t\t<div>
					<input type='submit' name='submit' value='Save' class='submit' />
				</div>
			</form>
		</div>
		
		<? \$this->LoadComponent('PageFooter'); ?>
	</body>
</html>");
			fclose($fp);
			@chmod($config->classes['ui'] . "/Form" . $object['_attributes']['phpName'] . ".php", 0777);
			}
		}
	}
	
	// == SQL SCRIPTS ======================================================================== //
	
	if ($_GET['db'])
	{
	foreach ($array['data'] as $object)
	{
		$table_name = Object::GetPlural($object['_attributes']['phpName']);
		$dao = new DAO();
				
		$sql_string = "DROP TABLE IF EXISTS `" . $table_name . "`;";
		if ($_GET['db'] == "print") echo "<hr />" . $sql_string;
		if ($_GET['db'] == "execute") $dao->ExecuteQuery($sql_string);
		$sql_string = "
			CREATE TABLE `" . $table_name . "`
			(";
		
		$separator = "";
		foreach ($object as $name => $attributes)
		{
			if ($name[0] != "_" && $attributes['type'] != "many")
			{
				$sql_string .= $separator
					. "`" . $name . "` "
					. (($attributes['type'] == 'timestamp') ? "INT" : strtoupper($attributes['type'])) . " "
					. (($attributes['precision']) ? "(" . $attributes['precision'] . ") " : "")
					. (($attributes['size']) ? "(" . $attributes['size'] . ") " : "")
					. (($attributes['required']) ? "NOT NULL " : "NULL ")
					. (($attributes['autoincrement']) ? "AUTO_INCREMENT " : "")
					. (($attributes['primaryKey']) ? "PRIMARY KEY " : "");
				$separator = ", \n";
			}
		}
		
		$sql_string .= ")
			ENGINE = MYISAM ;
		";
		
		if ($_GET['db'] == "print") echo "<hr />" . $sql_string;
		if ($_GET['db'] == "execute") $dao->ExecuteQuery($sql_string);
	}
	}
		
	echo "<hr />" . time() . "<hr /></pre>";
