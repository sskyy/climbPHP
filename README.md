#climbPHP
这是codeIgniter的扩展框架，提供了 “更好的模块化” “广播系统” “实体系统” 这三个主要的扩展功能。

#更好的模块化

我所指的模块是module，而不是model。model是业务模型，我们能用它进行一定的业务逻辑操作。而模块应该是系统的组成部分，应该实现三个主要特性：

1. 能根据系统事件动态地做出响应。也就是在系统级别应用 “观察者模式” 。
2. 能够使用系统统一的规则进行通信。climbPHP使用的是全局的广播。
3. 能够根据一定的规则构建出优雅的层次结构。

##climbPHP的解决方案：
1. 对于 “1” “2” 两点来说。climbPHP使用的是建立一个全局的事件对象。所有模块都可以通过这个事件对象进行 “抛出” 和 “监听” 。基础模块只需要在可能出现扩展的地方抛出合适的 “事件” 即可，除了自身逻辑以外不需要再知道其他模块的处理细节。这里注意，执行事件对象的“抛出”操作是可以得到返回值的，这个返回值代表着外界的响应。这使得在有必要的情况下外界可以在一定程度上干预模块内的操作。另外，全局事件也是一个模块。

2. 要构建出优雅的模块层次结构，需要实现 3 个要点：
	1. “被依赖”的模块有能力知道“依赖”它的模块何时被加载。并有机会操作“依赖”的它的模块；
	2. 要能实现依赖关系的自动加载；
	3. 模块能动态的按需获取其他模块，同时要求这种获取方法是不能污染全局变量的。

climbPHP实现了一个模块加载器（也是一个模块），能根据模块申明的依赖关系进行自动加载。加载的过程中也实现底层模块获取上层模块的能力。屏蔽了CI的全局对象，所有资源统一交由模块管理器来管理。


#广播系统

“广播系统”可以理解为对所有模块实现了一个全局的观察者模式。它是通过“事件对象”实现的。任何模块默认都可以使用全局的事件对象，也可以在模块内实现自己的“事件对象”。
事件对象是climbPHP的核心。除了事件“注册、触发”外，它还实现了很多强大的功能：

1. 可通过命名空间触发事件。事件的绑定支持使用后缀形式的命名空间。命名可以有多级。如`eventName.namspace2.namespace1`。
2. 可通过绑定带变量的事件。触发事件时，事件名称如果成功匹配带变量的事件，变量相应位置的字符串会作为参数传递给执行的操作。如，绑定时使用`event -> bind('/user/login/:variable','functionName')`。触发时使用`evetn -> trigger('user/login/value','value2' )`。操作接收到的参数为`value, value2`。
3. 对没有单一事件定向触发事件。即在触发事件时指定一个已经注册的操作来响应，监听了同一事件其他操作不会响应。
4. 可指定操作的执行顺序。在对事件进行绑定时，可以通过“first”，“last”，“before”，“after”等字段来指定当前绑定的操作的执行顺序。
5. 可在事件链中屏蔽某一事件。如果在事件中的操作继续触发了事件，那么会形成事件链。在触发事件时可以指定在其后的事件链中不触发哪些事件。


#核心模块
climbPHP已经经历了一次较大规模的重构，内置已经实现了一下几个核心模块：

* 持久化模块。climbPHP认为系统内的常见业务逻辑可以分为“实体操作”和“非实体操作”两类。“实体操作”指的像“用户、日志”等系统数据的增删该差操作。climbPHP为这些操作提供了常用的默认处理方法，用户只需要在模块中声明自己的实体名称，实体模块既能接受相应的事件，同时生成restFUL形式的接口。实体系统还实现了“实体”和“集合”两个类，除了基本的增删改查外，主要的特点有：
	*  实体实现了嵌套，的属性也可以是另一个实体。“集合”实现了“排序”、“筛选”等功能。
	*  实体和集合都通过两个组合的方式获取 “与数据库的连接” ，它只要求用户的数据库连接类实现了约定的接口。用户可以使用任何数据库，甚至混合使用。
	*  对于mysql用户。climbPHP通过一个小型ORM和一个表数据入口类来帮助使用实体类，并且可动态拆卸。
* DSL模块。DSL模块提供给上层模块一种快捷方式来基于已有的事件绑定来构建负责的业务逻辑。

#TODO list
1. ORM与持久化模块的整合。
2. 完整的示例。


#问题反馈

有任何问题或者你想加入climbPHP的开发都请email给我，谢谢。skyking_H@hotmail.com。




