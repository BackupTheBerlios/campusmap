package CampusMap;

import processing.core.*;
/**
 * @author Gunnar
 *
 */
public class Overview extends GuiObject{
	
	private final int CONTENT_REFRESH_RATE = 0;
	private int currentRefreshState=CONTENT_REFRESH_RATE;
	private boolean drawnOnce=false;

	private PApplet applet;
	private PImage img;
	private static FVector camPos;
	private FVector[] controlPoints;
	private boolean bezierBool;
	private boolean imgInited = false;
	
	public float viewMultiplicatorX=14;
	public float viewMultiplicatorY=14;
	
	public Overview(int p_x, int p_y, int p_w, int p_h) {
		super(p_x,p_y,p_w,p_h, null);
		x=p_x; y=p_y; w=p_w; h=p_h;
		controlPoints = new FVector[4];
		bezierBool=false;
	}
	
	public void setImage(PImage p_img){
		img = p_img;
	}
	
	public void setBezierBool(boolean p_bezier){
		bezierBool = p_bezier;
	}
	
	public static void setLookPoint(FVector look){
		camPos=look;
	}
	public void setControlPoints(FVector[] ctrlPnts){
		controlPoints=ctrlPnts;
	}
	
	public boolean check(int mouseX, int mouseY, boolean mouseJustReleased, boolean mouseJustPressed){
		boolean back=false;
		if(img!=null && drawnOnce==false){
			back = true;
//			drawnOnce=true;
		}else if(CampusMap.overviewImage!=null && !imgInited){
			img = new PImage(CampusMap.overviewImage.width/3, CampusMap.overviewImage.height/3);
			img.loadPixels();
			for(int heightIndex=0;heightIndex<img.height;heightIndex++)
				for(int widthIndex=0;widthIndex<img.width;widthIndex++)
					img.set(widthIndex, heightIndex, 
							CampusMap.overviewImage.get(widthIndex*3, heightIndex*3));
			imgInited=true;
		}
		return back;
	}
	
	public void draw(PApplet applet){
//		System.out.println("draw Overview");
		if(img!=null){
			applet.image(img, x, y);
			// output of movement and position

			int originX = x+img.width/2;
			int originY = y-14+img.height/2;

			if(camPos!=null){
				applet.noStroke();
				applet.pushMatrix();
				float locatorX = (float)(originX+(camPos.getX()/12));
				float locatorY = (float)(originY+(camPos.getY()/13));
				applet.translate(locatorX,locatorY);
//				System.out.println("camPos: "+camPos+", locators: "+locatorX+" "+locatorY);
				applet.fill(255,0,0);
				applet.sphere(5);
				applet.popMatrix();
			}
			/*
			if(controlPoints[0]!=null)
				for(int i=0;i<controlPoints.length;i++){
					if(i<2)applet.fill(255,0,0);else applet.fill(0,255,0);
					applet.pushMatrix();
					applet.translate(controlPoints[i].getX()/3,controlPoints[i].getY()/3);
					applet.sphere(5);
					applet.popMatrix();
				}
			*/
		}else drawnOnce=false;	
	}
}
