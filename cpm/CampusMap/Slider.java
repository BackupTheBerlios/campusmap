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
package CampusMap;

import processing.core.*;


//General class for sliders
public class Slider extends GuiObject{
	
	private boolean remotelyChanged;
	
	Slider(int _x, int _y, int _w, int _h, float activeArea, float _val, boolean _vertical) {
		super(_x,_y,_w,_h, null);
		vertical=_vertical;
		activeArea = Math.max(0.0f, activeArea);
		activeArea = Math.min(1.0f, activeArea);
		if (vertical) {
			minVal= (int)(y + (1.0f - activeArea)*h);
			maxVal= (int)(y + (activeArea)*h);
		} else {
			minVal= (int)(x + (1.0f - activeArea)*w);
			maxVal= (int)(x + (activeArea)*w);
		}
		grabbed = false;
		remotelyChanged=false;
		if (_val < 0.0f || _val > 1.0f)	_val = 0.5f;
		val = (int)(minVal + _val * (maxVal - minVal));
	}

	
	public float getValue() {
		return ((float)(val - minVal)) / ((float)(maxVal - minVal));
	}
	
	public void setValue(float _val) {
		float oldVal = val;
		if (_val < 0.0f) _val = 0.0f;
		if (_val > 1.0f) _val = 1.0f;
		val = (int)(minVal + _val * (maxVal - minVal));			
		if(oldVal!=val)remotelyChanged=true;
	}

	public boolean registerExecution(String methodName, Object objectReference, CampusMap applet) {
		executeObject = objectReference;
		Float floatValue = new Float(0);
		Boolean boolValue = new Boolean(true);
		try {
			executeMethod = executeObject.getClass().getMethod(methodName, new Class[] {floatValue.getClass(), boolValue.getClass()});
			if (methodName.equals("changeHeight"))
				applet.theCamera.registerHeightSlider((Slider)this);
		} catch (Exception e) {
			return false;
	    }
		return true;
	}
	
	public void execute(boolean mouseJustPressed) {
		if (executeMethod != null && active) {
		    try {
		    	executeMethod.invoke(executeObject, new Object[]{ new Float(getValue()) , new Boolean(mouseJustPressed)} );
		    }	catch (Exception e) {
			      System.err.println("Disabling execute slider event because of an error.");
			      e.printStackTrace();
			      executeMethod = null;
		    }
		}
	}
	
	public boolean check(int mouseX, int mouseY, boolean mouseJustPressed, boolean mouseJustReleased) {
		boolean back;
		//turn movement off
		if (grabbed && mouseJustReleased) {
			grabbed = false;
		}
		//make active
		else if (!grabbed && mouseJustPressed && (mouseX > x-TOLERANCE && mouseX < x+w+TOLERANCE)
				&& (mouseY > y-TOLERANCE && mouseY <y+h+TOLERANCE)) {
			grabbed = true;
			if (vertical) val = mouseY;
			else val = mouseX;
			val = Math.max(minVal, val);
			val = Math.min(maxVal, val);
		}
		//keep active and refresh value
		else if (grabbed && !mouseJustReleased) {
			if (vertical) val = mouseY;
			else val = mouseX;
			val = Math.max(minVal, val);
			val = Math.min(maxVal, val);
		}

		if (grabbed) {
			execute(mouseJustPressed);
		}

		if(remotelyChanged){
			back=true;
			remotelyChanged=false;
		}else back=grabbed;

		return back;
	}	
	
	public void draw(PApplet applet) {
		if(applet!=null){
			//System.out.println("drawing Slider");
			applet.fill(inactiveCol.r, inactiveCol.g, inactiveCol.b);
			applet.stroke(inactiveCol.r/2, inactiveCol.g/2, inactiveCol.b/2);
			if(vertical) applet.rect(x+w/2-2,y,4,h);//applet.line(x+w/2,y,x+w/2,y+h);
			else applet.line(x,y+h/2,x+w,y+h/2);
			
			if (grabbed) applet.stroke(activeCol.r+50, activeCol.g+50, activeCol.b+50);
			else applet.stroke(activeCol.r/2, activeCol.g/2, activeCol.b/2);
			applet.fill(activeCol.r, activeCol.g, activeCol.b);
			if(vertical) applet.rect(x,val-3, w,6);
			else applet.rect(val-2,y, 4,h);
		}
	}
}