<?php
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    \think\middleware\SessionInit::class,
    // 屏蔽垃圾爬虫
    \app\common\middleware\AntiGarbageReptile::class,
    // 全局跨域请求
    // \think\middleware\AllowCrossDomain::class,
];
