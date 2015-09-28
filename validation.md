# 验证

## 所属命名空间 `App\Lib\Validation

## 使用方法 

```php

$validata = new Validation();
$error = $validata->make( $data , array(
				'name' => 'required',
				'email' =>'unique:users|email|required',
				'pwd' => 'confirm:cpwd|min:6|required'
			));
			
			//$data 要验证的关联数组
			//返回 true false  获取错误信息用 $validata->$error;

```

## 主要参数
 	关联数组：key 要验证的字段， value 包括验证的规则，用 | 分割
	
| 关键字 | 规则 |
|:---|:---|
| required | 必须 |
| number | 数字 |
| email | 邮箱 |
| phone | 手机号 |
| url | 完整的网址 |
| length | 长度 用 length:6-10 表示长度必须在6到10之间 |
| min | 最小长度 用 min:6 最小长度为6 |
| max | 最大长度 用 max:10 最大长度为10 |
| regular | 自定义正则表达式验证 regular: |
| confirm | 相同 confirm:id 当前验证的数据必须与数据中的id相同 |
| conform | 相等 conform:123 当前验证的数据必须与123相等 |
| unique | 唯一 unique:table.id 当前验证的数据必须在数据库中table表中id字段是唯一的 |

