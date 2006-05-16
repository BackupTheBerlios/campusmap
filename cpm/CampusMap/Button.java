package CampusMap;


import processing.core.*;
/*
 * Created on 31.05.2005
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

//general class for a button
public class Button extends GuiObject {
	static	final	int	OFF			= 0;
	static	final	int	ON			= 1;
		
	boolean stateful;
	private int state;
	private Color[] fillColors;
	private String imgName;
	private PImage img;
	private int[] imgOffset;
	private boolean respondOnReleased;
	private boolean mouseOverState;
	private boolean lastMouseOverState;
	
	Button(int p_x,int p_y,int p_w,int p_h, String p_img, int[] p_offsetButton, boolean p_stateful, String helpText_p) {
		super(p_x,p_y,p_w,p_h,helpText_p);
		if(p_img!=null){
			imgName=Environment.address+Environment.ressourceFolder+p_img;
			imgOffset=p_offsetButton;
		}
		stateful		= true;
		state		= OFF;
		respondOnReleased	= true;
	}

	public void setColor(int r0, int g0, int b0, int r1,int g1,int b1) {
		fillColors	  = new Color[2];
		fillColors[0] = new Color(r0, g0, b0, 255);
		fillColors[1] = new Color(r1, g1, b1, 255);
	}

	public void SetRespondOnReleased(boolean _respondOnReleased) {
		respondOnReleased = _respondOnReleased;
	}

	public boolean registerExecution(String methodName, Object objectReference, CampusMap applet) {
		executeObject = objectReference;
		try {
			executeMethod = executeObject.getClass().getMethod(methodName, new Class[] {});
		} catch (Exception e) {
			System.err.println("No such method for Button: "+methodName);
			return false;
	    }
		return true;
	}
	
	public void execute(boolean mouseJustPressed) {
		if (executeMethod != null && active) {
			//System.out.println("Button pressed");
		    try {
		    	executeMethod.invoke(executeObject, new Object[]{} );
		    }	catch (Exception e) {
			      System.err.println("Disabling execute slider event because of an error.");
			      e.printStackTrace();
			      executeMethod = null;
		    }
		}
	}
	

	public boolean check(int mouseX,int mouseY, boolean mouseJustReleased, boolean mouseJustPressed) {
		boolean backValue=false;
		lastMouseOverState = mouseOverState;
		if( (mouseX >= x)	&&	(mouseX <= x + w)	&&	(mouseY >= y)	&&	(mouseY <= y + h) ) {
			mouseOverState=true;
			if(helpText!=null)Environment.setToolTip(helpText+"", 0);
			if(stateful)state = state==OFF?ON:OFF;
			backValue = (respondOnReleased?mouseJustReleased:mouseJustPressed);
		}else {
			mouseOverState=false;
			if(lastMouseOverState)Environment.clearToolTip();
		}
		if(backValue==true)execute(true);
		
		//load image and set update flag to true if loaded
		if(img == null){
			if(imgName != null && applet!=null){
				img = applet.loadImage(imgName);
				backValue=true;
			}
		}
		
		return backValue;
	}

	public void draw(PApplet p_applet) {
		if(visible){
			applet=p_applet;
			if (state==ON)	applet.stroke(0,0,0,150);
			else			applet.stroke(150,150,150,150);
			if(img==null){
				applet.fill(fillColors[state].r, fillColors[state].g, fillColors[state].b, fillColors[state].a);
				applet.rect(x,y,w,h);
			}
			else {
				applet.noFill();
				applet.image(img, x+imgOffset[0], y+imgOffset[1]);
			}
			applet.fill(255);
		}
	}
}