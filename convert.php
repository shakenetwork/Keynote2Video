<?php

// 将 以下路径改为 LazyAudioBook 项目在本机的路径

$audio_path = "/Users/Easy/Code/gitcode/LazyAudioBook/";

// ======= 以下不用修改 ==========

define( "DATA_DIR" , __DIR__ . '/data' );
$delete = [];

// 首先将目录中的txt文件都转为mp3 
foreach( glob(DATA_DIR . "/*.txt") as $file )
{
    $delete[] = $file;
    // 判断txt里边是否有内容。没有就不转mp3了
    $content = file_get_contents( $file );
    if( strlen( $content ) < 1 ) continue;
    
    $to = str_replace(".txt",".mp3",$file);
    system("cd $audio_path & robo convert $file $to 3  --load-from $audio_path");
}
$list_file = DATA_DIR . "/videos.txt";
@unlink( $list_file );

// 然后开始遍历图片
foreach( glob(DATA_DIR . "/*.png") as $image )
{
    $delete[] = $image;
    $reg = '/data\.([0-9]+)\.png/is';
    if( preg_match( $reg , $image , $out ) )
    {
        $audio_file = DATA_DIR.'/data_'.intval($out[1]).'.mp3';
        $video_file = DATA_DIR.'/data_'.intval($out[1]).'.ts';
        $delete[] = $video_file;

        // 如果图片没有对应的音频 那么采用5秒静音音频
        if( !file_exists( $audio_file ) )
           $audio_file = __DIR__. '/silence.mp3';
        else
        $delete[] = $audio_file;
           
           
        // 开始合成mp4
        
        //$cmd = "ffmpeg -loop 1 -i $image -i $audio_file -c:a copy -c:v libx264 -shortest -pix_fmt yuv420p -vf scale=1080:-1 $video_file";

        // $cmd = "ffmpeg -loop 1 -i $image -i $audio_file -c:a copy -c:v libx264 -shortest -vf scale=1080:-1 $video_file";

        $cmd = "ffmpeg -loop 1 -i $image -i $audio_file -q:v 1 -c:a copy  -shortest $video_file";

        echo $cmd;

        system( $cmd );

        // 将视频文件写入list
        file_put_contents( $list_file , "file './" .  basename( $video_file ). "'\r\n" , FILE_APPEND  );
    }
}

// 合并所有视频
$final = DATA_DIR . '/final.mp4';
system("ffmpeg -safe 0 -f concat -i $list_file -c:v libx264 $final");

echo "Done";

//system("open " . DATA_DIR );
system("open $final" );

$delete[] = $list_file;

foreach( $delete as $todelete )
{
    @unlink( $todelete );
}

