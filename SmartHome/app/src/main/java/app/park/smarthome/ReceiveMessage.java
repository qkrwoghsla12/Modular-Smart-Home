package app.park.smarthome;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.BitmapFactory;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.NotificationCompat;

import com.google.firebase.messaging.RemoteMessage;

/**
 * Created by 박재호 on 2017-08-18.
 */

public class ReceiveMessage extends com.google.firebase.messaging.FirebaseMessagingService{
    String ServerHost;
    int img;
    SharedPreferences host;
    static int cnt=0;
    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        ServerHost = remoteMessage.getData().get("ip");
        host = getSharedPreferences("one",0);
        SharedPreferences.Editor editor = host.edit();
        editor.putString("host",ServerHost);
        editor.commit();
        sendNotification(remoteMessage.getData().get("title"),remoteMessage.getData().get("body"),remoteMessage.getData().get("number"));
    }

    private void sendNotification(String title,String body,String SNumber) {
        Intent intent = new Intent(this, MainActivity.class);
        Bundle bundle = new Bundle();
        bundle.putString("ServerHost",ServerHost);
        intent.putExtras(bundle);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        PendingIntent pendingIntent = PendingIntent.getActivity(this,0,intent,PendingIntent.FLAG_UPDATE_CURRENT);
        Uri defaultSoundUri= RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
        img = R.mipmap.ic_launcher;

        switch(SNumber){
            case "1":
                img = R.mipmap.one;
                break;
            case "2":
                img = R.mipmap.two;
                break;
            case "3":
                img = R.mipmap.three;
                break;
            case "4":
                img = R.mipmap.four;
                break;
            default:
                img = R.mipmap.ic_launcher;
                break;
        }

        NotificationCompat.Builder notificationBuilder = new NotificationCompat.Builder(this)
                .setSmallIcon(R.mipmap.ic_stat_send)
                .setLargeIcon(BitmapFactory.decodeResource(getApplicationContext().getResources(),img))
                .setColor(0xff00ff)
                .setContentTitle(title)
                .setContentText(body)
                .setAutoCancel(true)
                .setSound(defaultSoundUri)
                //.setStyle(new NotificationCompat.BigPictureStyle().setSummaryText(body).bigPicture(cap))
                .setContentIntent(pendingIntent)
                .setAutoCancel(true)
                .setStyle(new NotificationCompat.BigTextStyle().setBigContentTitle(title).bigText(body));

        NotificationManager notificationManager =
                (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);

        notificationManager.notify(cnt++, notificationBuilder.build());
    }
}
