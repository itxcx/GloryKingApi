<?php
namespace GloryKing\Module;

use GloryKing\Base\ImageBase;
use Library\ErrorMessage\ErrorMessage;
use Library\SimpleHtml\CURL;
use Library\SimpleHtml\SimpleHtml;
use Library\UploadFile\UploadFile;

/**
 * 通用模块
 *
 * Class CommonModule
 * @package GloryKing\Module
 * @author jiangxianli
 * @created_at 2017-04-21 17:37:24
 */
class CommonModule extends Module
{
    /**
     * 上传图片
     *
     * @param $file
     * @return \GloryKing\Model\Image|ErrorMessage
     * @author jiangxianli
     * @created_at 2017-04-21 17:44:35
     */
    public static function uploadImage($file)
    {
        //初始化上传器
        $upload = new UploadFile(['jpg', 'png'], 20 * 1024);
        //上传文件
        $file = $upload->upload($file, 'hero');

        //错误校验
        if (ErrorMessage::isError($file)) {
            return $file;
        }

        //保存图片
        $params = [
            'url'       => $upload->getFileUrl(),
            'path'      => $upload->getFilePath(),
            'extension' => $upload->getFileExtension()
        ];
        return ImageBase::addImage($params);
    }

    /**
     * 解析网络地址
     *
     * @param $from_url
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-25 15:56:40
     */
    public static function parseVideoUrl($from_url)
    {
        $page_content = CURL::get($from_url, parse_url($from_url, PHP_URL_HOST));
        $page_content = mb_convert_encoding($page_content, 'utf-8', 'GBK,UTF-8,ASCII');
        $html         = SimpleHtml::str_get_html($page_content);

        if (starts_with($from_url, 'https://m.v.qq.com')) {
            //获取视频结点
            $video_node = $html->find('#tenvideo_video_player_0', 0);
            $src        = $video_node ? $video_node->src : '';
            if (!$src) {
                return [];
            }

            $response = [
                'url' => $src
            ];

            //获取封面图结点
            $video_node = $html->find('.tvp_poster_img', 0);
            $style      = $video_node ? $video_node->style : '';
            if ($style) {
                //匹配封面图地址
                $matched = preg_match('/(https|http)?:\/\/[^\s]+\.(jpg|png|gif|jpeg)/', $style, $matches);
                if ($matched && $matches) {
                    $response['image'] = ImageBase::saveInternetImage($matches[0]);
                }
            }
            return $response;
        }
        //https://m.v.qq.com/x/cover/9/9ud9svo40zvaxlb.html?vid=v0396t870fu
    }
}