FORMAT: <?=$this->format?>

HOST: <?=$this->host?>


# <?=$this->name?>


<?=$this->description?>


<?php foreach($this->groups as $group)://L1?>
## Group <?=$group->name?>


<?=$group->description?>

<?php foreach($group->resources as $resource)://L2?>

## <?=$resource->name?> [<?=$resource->path?>]

<?=$resource->description?>


<?php foreach($resource->examples as $action):?>
### <?php echo $action->name;?> [<?=$action->request->method?>]

<?=$action->description?>


+ Request (<?=$action->request->type?>)

<?php if(count($action->request->headers)):?>
    + Headers

<?php foreach($action->request->headers as $k=>$v):?>
<?=pad(sprintf("+ %s: %s", $k, $v), 8)."\n";?>
<?php endforeach?>

<?php endif;?>
<?php if(count($action->request->parameters)):?>
    + Parameters

<?php foreach($action->request->parameters as $k=>$v):?>
<?=pad(sprintf("+ %s: %s (%s)", $k, $v[0], $v[1]), 8)."\n";?>
<?php endforeach?>

<?php endif;?>
<?php if($action->request->body):?>
    + Body
    
<?=pad($action->request->body, 12);?>

<?php endif;?>

+ Response <?=$action->response->code?> (<?=$action->response->type?>)

<?php if($action->response->body):?>
    + Body
    
<?=pad($action->response->body, 12);?>

<?php endif;?>

<?php endforeach//-L3?>
<?php endforeach;//-L2?>

<?php foreach($group->examples as $action)://L22?>

## <?=$action->name?> [<?=$action->request->path?>]

### <?=$action->name?> [<?=$action->request->method?>]

<?=$action->description?>

+ Request (<?=$action->request->type?>)

<?php if(count($action->request->headers)):?>
    + Headers

<?php foreach($action->request->headers as $k=>$v):?>
<?=pad(sprintf("+ %s: %s\n", $k, $v), 8);?>
<?php endforeach?>

<?php endif;?>
<?php if(count($action->request->parameters)):?>
    + Parameters

<?php foreach($action->request->parameters as $k=>$v):?>
<?=pad(sprintf("+ %s: %s (%s)", $k, $v[0], $v[1]), 8)."\n";?>
<?php endforeach?>

<?php endif;?>
<?php if($action->request->body):?>
    + Body
    
<?=pad($action->request->body, 12);?>

<?php endif;?>

+ Response <?=$action->response->code?> (<?=$action->response->type?>)

<?php if($action->response->body):?>
    + Body
    
<?=pad($action->response->body, 12);?>

<?php endif;?>

<?php endforeach//-L22?>

<?php endforeach;//api->groups?>
