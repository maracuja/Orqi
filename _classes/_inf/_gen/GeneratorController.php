<?
	class GeneratorController extends Controller
	{
		function Run()
		{
			// the default generator stuff happens in here
			$config = Config::GetInstance();
			$objects = sfYaml::load('_config/schema.yml');
	
			foreach ($objects as $object)
			{
				// search for xml files containing file schemas to generate
				foreach ($files as $file)
				{
					// parsexml
					// loop through the xml and yaml to generate the files

					// this is example of model
//					RenderHelper::RenderClassDeclaration($object, 'Base#objectname#', 'Object');
//					{
//						RenderHelper::RenderConstructor($object);
//						
//						foreach ($fields as $field) { 
//							RenderHelper::RenderField($field, 'function Get#Field#() { return $this->#field#; }');
//							RenderHelper::RenderField($field, 'function Set#Field#(#field#=\'\') { $this->#field# = $#field#; }');
//							 if ($field['type'] == 'many') {
//								RenderHelper::RenderAssociation($field);
//							 } 
//						 }
//					}
//					
//					class RenderHelper
//					{
//						function RenderClassDeclaration($object, $format='#Objectname#', $extends) {}
//						function RenderConstructor($object) {}
//						function RenderField($field, $format='') {}
//						function RenderFields($fields=array(), $format='', $separator='') {}
//						
//						function RenderAssociation($field) {}
//					}
				}
			}
			
			// TODO think of a way to allow for code generation of different frameworks
		}
	}