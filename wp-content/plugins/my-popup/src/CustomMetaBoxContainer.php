<?php

namespace Wpshop\PluginMyPopup;

use WP_Post;
use Wpshop\MetaBox\MetaBoxContainer\AbstractMetaBoxContainer;

class CustomMetaBoxContainer extends AbstractMetaBoxContainer {

    /**
     * @param WP_Post $post
     *
     * @return string
     */
    public function render( WP_Post $post ) {
        $html = <<<HTML
		
		
<ul class="wpshop-metabox-tabs" role="tablist">
	<li class="wpshop-metabox-tab">
		<a class="wpshop-metabox-tab-link active" id="tab_general" data-toggle="tab" href="#tab_box_general" role="tab" aria-controls="tab_box_general" aria-selected="true">Основные настройки</a>
	</li>
	<li class="wpshop-metabox-tab">
		<a class="wpshop-metabox-tab-link" id="tab_design" data-toggle="tab" href="#tab_box_design" role="tab" aria-controls="tab_box_design" aria-selected="false">Внешний вид</a>
	</li>
	<li class="wpshop-metabox-tab">
		<a class="wpshop-metabox-tab-link" id="tab_content" data-toggle="tab" href="#tab_box_content" role="tab" aria-controls="tab_box_content" aria-selected="false">Контент</a>
	</li>
	<li class="wpshop-metabox-tab">
		<a class="wpshop-metabox-tab-link" id="tab_rules" data-toggle="tab" href="#tab_box_rules" role="tab" aria-controls="tab_box_rules" aria-selected="false">Правила</a>
	</li>
</ul>

<div class="wpshop-metabox-tabs-content">
	<div class="wpshop-metabox-tab-content active" id="tab_box_general" role="tabpanel" aria-labelledby="tab_general">
	
		<div class="wpshop-meta-header">Расположение и размеры попап на странице</div>
		
		<div class="wpshop-meta-row">
		
			<div class="mypopup-sizes-container">
		
				<div class="mypopup-position">
					<input type="radio" name="position" id="mypopup_position_top_left" value="top_left">
					<label for="mypopup_position_top_left">Top left</label>	
					<input type="radio" name="position" id="mypopup_position_top_center" value="top_center">
					<label for="mypopup_position_top_center">Top center</label>	
					<input type="radio" name="position" id="mypopup_position_top_right" value="top_right">
					<label for="mypopup_position_top_right">Top left</label>	
					<input type="radio" name="position" id="mypopup_position_center_left" value="center_left">
					<label for="mypopup_position_center_left">Center left</label>	
					<input type="radio" name="position" id="mypopup_position_center_center" value="center_center" checked>
					<label for="mypopup_position_center_center">Center center</label>	
					<input type="radio" name="position" id="mypopup_position_center_right" value="center_right">
					<label for="mypopup_position_center_right">Center right</label>	
					<input type="radio" name="position" id="mypopup_position_bottom_left" value="bottom_left">
					<label for="mypopup_position_bottom_left">Bootom left</label>	
					<input type="radio" name="position" id="mypopup_position_bottom_center" value="bottom_center">
					<label for="mypopup_position_bottom_center">Bottom center</label>	
					<input type="radio" name="position" id="mypopup_position_bottom_right" value="bottom_right">
					<label for="mypopup_position_bottom_right">Bottom right</label>			
				</div>
				
				<div class="mypopup-sizes">
				
					<div class="mypopup-sizes__width">
						<label for="" class="mypopup-sizes__label">Ширина:</label>
						
						<span class="wpshop-meta-field-inline">
							<input type="number" class="wpshop-meta-field--size-xs" size="4">
						</span>
						
						<select name="" id="">
							<option value="min">px</option>
							<option value="min">vw</option>
						</select>
					</div>
				
					<div class="mypopup-sizes__height">
						<label for="" class="mypopup-sizes__label">Высота:</label>
						
						<span class="wpshop-meta-field-inline">
							<input type="number" class="wpshop-meta-field--size-xs" size="4">
						</span>
						
						<select name="" id="">
							<option value="min">px</option>
							<option value="min">vh</option>
						</select>
					</div>
					
					<div class="mypopup-sizes__description">
						vw и vh — единицы измерения, относительно ширины и высоты браузера<br>
						100vw — обозначает 100% от ширины браузера, то есть попап будет на всю ширину
					</div>
					
				</div>
			
			</div>
			
		</div>
		
		<div class="wpshop-meta-row">
		
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Показывать повторно
			</label>
			
			через
			
			<span class="wpshop-meta-field-inline">
				<input type="number" class="wpshop-meta-field--size-xs" size="4">
			</span>
			
			<select name="" id="">
				<option value="min">минут</option>
				<option value="min">часов</option>
				<option value="min">дней</option>
			</select>
			
		</div>
		
		<div class="wpshop-meta-row">
		
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Включить фоновую подложку
			</label>
			
		</div>
		
		<div class="wpshop-meta-row">
		
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Показать кнопку закрытия
			</label>
			
			через
			
			<span class="wpshop-meta-field-inline">
				<input type="number" class="wpshop-meta-field--size-xs" size="4">
			</span>
			
			сек.
			
		</div>
		
		<div class="wpshop-meta-row">
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Запретить закрытие попап по клику на подложку
			</label>
		</div>
		
		<div class="wpshop-meta-row">
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Запретить закрытие попап по клавише ESC
			</label>
		</div>
		
		<div class="wpshop-meta-row">
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Отключить прокрутку страницы
			</label>
		</div>
		
		<div class="wpshop-meta-row">
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> Закрыть попап через
			</label>
			
			через
			
			<span class="wpshop-meta-field-inline">
				<input type="number" class="wpshop-meta-field--size-xs" size="4">
			</span>
			
			сек.
		</div>
		
		<div class="wpshop-meta-header">Отображение на устройствах</div>
		
		<div class="wpshop-meta-row">
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> На компьютере
			</label>
			
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> На планшете
			</label>
			
			<label class="wpshop-meta-checkbox">
				<input type="checkbox">
				<span class="wpshop-meta-checkbox__label"></span> На мобильном
			</label>
		</div>
		
			
	</div>
	
	<div class="wpshop-metabox-tab-content" id="tab_box_design" role="tabpanel" aria-labelledby="tab_design">
	
		<div class="wpshop-meta-header">Внешний вид попап</div>
		
		<div class="wpshop-meta-row">
			<div class="wpshop-meta-field">
				<div class="wpshop-meta-field__label">
					<label>Фоновый цвет</label>			
				</div>
				<div class="wpshop-meta-field__body">
					<span class="wpshop-meta-field-inline">
						<input type="text" name="" id="" class="js-wpshop-color-picker" value="#ffffff">
					</span>
					
					прозрачность
					
					<span class="wpshop-meta-field-inline">
						<input type="number" class="wpshop-meta-field--size-xs" size="4">
					</span>
					
					%
				</div>
			</div>
		</div>
		
		<div class="wpshop-meta-row">
			<div class="wpshop-meta-field">
				<div class="wpshop-meta-field__label">
					<label>Фоновая картинка</label>			
				</div>
				<div class="wpshop-meta-field__body">
				
					<button type="button" name="0" class="button js-wpshop-form-element-browse" value="">Выбрать</button>
				
					<span class="wpshop-meta-field-inline">
						<select name="" id="">
							<option value="top_left">Сверху слева</option>
							<option value="top_center">Сверху по центру</option>
							<option value="top_right">Сверху справа</option>
							<option value="center_left">По середине слева</option>
							<option value="center_center" selected="selected">По середине по центру</option>
							<option value="center_right">По середине справа</option>
							<option value="bottom_left">Снизу слева</option>
							<option value="bottom_center">Снизу по центру</option>
							<option value="bottom_right">Снизу справа</option>
						</select>
					</span>
					
					<span class="wpshop-meta-field-inline">
						<select name="" id="">
							<option value="min">не повторять</option>
							<option value="min">повторять по X</option>
							<option value="min">повторять по Y</option>
						</select>
					</span>
					
					<span class="wpshop-meta-field-inline">
						<select name="" id="">
							<option value="min">cover (растянуть)</option>
							<option value="min">contain</option>
						</select>
					</span>
				</div>
			</div>
		</div>
		
		<div class="wpshop-meta-row">
			<div class="wpshop-meta-field">
				<div class="wpshop-meta-field__label">
					<label>Рамка</label>			
				</div>
				<div class="wpshop-meta-field__body">
					
					<span class="wpshop-meta-field-inline">
						<input type="number" class="wpshop-meta-field--size-xs" size="4">
					</span>
					
					px
					
					<span class="wpshop-meta-field-inline">
						<select name="" id="">
							<option value="min">solid (сплошная)</option>
							<option value="min">dotted</option>
							<option value="min">dashed</option>
							<option value="min">double</option>
						</select>
					</span>
					
					<span class="wpshop-meta-field-inline">
						<input type="text" name="" id="" class="js-wpshop-color-picker" value="#ffffff">
					</span>
					
					скругление
					
					<span class="wpshop-meta-field-inline">
						<input type="number" class="wpshop-meta-field--size-xs" size="4">
					</span>
					
					px
					
				</div>
			</div>
		</div>
		
		<div class="wpshop-meta-row">
			<div class="wpshop-meta-field">
				<div class="wpshop-meta-field__label">
					<label>Тень</label>			
				</div>
				<div class="wpshop-meta-field__body">
					
					<span class="wpshop-meta-field-inline">
						<select name="" id="">
							<option>Вариант 1</option>
							<option>Вариант 2</option>
							<option>Вариант 3</option>
						</select>
					</span>
					
					<span class="wpshop-meta-field-inline">
						<input type="text" name="" id="" class="js-wpshop-color-picker" value="#ffffff">
					</span>
					
					прозрачность
					
					<span class="wpshop-meta-field-inline">
						<input type="number" class="wpshop-meta-field--size-xs" size="4">
					</span>
					
					%
					
				</div>
			</div>
		</div>
		
		
		<div class="wpshop-meta-header">Контент</div>
		
		<div class="wpshop-meta-field">
			<div class="wpshop-meta-field__label">
				<label>Label</label>			
			</div>
			<div class="wpshop-meta-field__body">
				lol
			</div>
		</div>
		
	</div>
	
	<div class="wpshop-metabox-tab-content" id="tab_box_content" role="tabpanel" aria-labelledby="tab_content">
		<div class="wpshop-meta-header">Заголовок</div>
	</div>
	
	<div class="wpshop-metabox-tab-content" id="tab_box_rules" role="tabpanel" aria-labelledby="tab_rules">
		<div class="wpshop-meta-header">События, при которых показывать попап</div>
		
		...
		
		<div class="wpshop-meta-header">На каких страницах выводить попап</div>
				
		<p>По умолчанию попап будет отображаться на всех страницах, но Вы можете задать ниже свои условия для вывода.</p>
		
		<!--<div class="wpshop-meta-row">-->
			<!--<div class="mypopup-rules js-mypopup-rules">-->
						<!---->
			<!--</div>-->
			<!--<div class="mypopup-rule-dump js-mypopup-rule-dump">-->
			<!---->
				<!--<div class="mypopup-rule js-mypopup-rule">-->
					<!--<div class="mypopup-rule__visible">-->
						<!--<select name="" id="">-->
							<!--<option value="show">Показать</option>-->
							<!--<option value="hide">Скрыть</option>-->
						<!--</select>-->
					<!--</div>-->
					<!--<div class="mypopup-rule__type js-mypopup-rule-type">-->
						<!--<select name="" id="">-->
							<!--<option value="home">на главной</option>-->
							<!--<option value="posts">в записях</option>-->
							<!--<option value="pages">на страницах</option>-->
							<!--<option value="categories">в рубриках</option>-->
							<!--<option value="tags">в тегах</option>-->
							<!--<option value="search">на странице поиска</option>-->
							<!--<option value="404">на 404</option>-->
						<!--</select>-->
					<!--</div>-->
					<!---->
					<!--<div class="mypopup-rule__value js-mypopup-rule-value">-->
						<!--<input type="text">-->
					<!--</div>-->
					<!---->
					<!--<div class="mypopup-rule__remove">-->
						 <!--<span class="mypopup-rule-remove js-mypopup-rule-remove">&times;</span>-->
					<!--</div>-->
				<!--</div>-->
			<!---->
			<!--</div>-->
			<!--<span class="button js-mypopup-rule-add">Add rule</span>-->
		<!--</div>-->
	</div>
</div>

HTML;

        echo $html;
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function save( WP_Post $post ) {
    }
}
