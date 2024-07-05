<?php
//  +----------------------------------------------------------------------
//  | huicmf [ huicmf快速开发框架 ]
//  +----------------------------------------------------------------------
//  | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
//  +----------------------------------------------------------------------
//  | Author: 小灰灰 <762229008@qq.com>
//  +----------------------------------------------------------------------
//  | Info:  图像处理
//  +----------------------------------------------------------------------
//

namespace plugin\admin\app\common;

use support\Request;
use Intervention\Image\ImageManagerStatic as Image;

class ImageInterver
{

    protected $img;

    /*public function __construct($imgSrc)
    {
        $this->img = Image::make($imgSrc);
    }*/

    /**
     * 修改指定图片大小
     *
     * @param $imgSrc   指定图片地址
     * @param $up       是否开启调整
     * @param $maxWidth 指定图片最大宽度
     *
     * @return \Intervention\Image\Image
     */
    public static function editImgSize($imgSrc, $up = false, $maxWidth = 1000)
    {
        if ($up === false) {
            return;
        }
        $img = Image::make($imgSrc);
        $img->widen($maxWidth, function ($constraint) {
            $constraint->upsize();
        });
        $img->save($imgSrc);
    }

    /**
     * 图片添加水印
     *
     * @param $imgSrc
     * @param $waterImg 水印图片名称
     *
     * @return \Intervention\Image\Image
     */
    public static function addWater($imgSrc, $waterImg = 'mark.png', $position = 'bottom-right', $touming = 100)
    {
        $waterImgSrc = public_path()."/static/water/".$waterImg;
        //处理水印图透明度
        //2.0版本
        $watermark = Image::make($waterImgSrc);
        $watermark->opacity($touming);
        $img = Image::make($imgSrc);
        $img->insert($watermark, $position);
        $img->save($imgSrc);
    }

}
