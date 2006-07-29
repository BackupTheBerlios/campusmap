/**
 * 
 */
package CampusMap;

import processing.core.*;

import java.util.Vector;

/**
 * @author kriegerischerKämpfer
 *
 *  Still two cases: 
 * 
 */
public class SlideCase extends PApplet {
	//Objects
	Vector content;
	
	static final int RIGHT=0, BOTTOM=1, LEFT=2, TOP=3;
	public static final int SHIFTED_IN=0, SHIFTED_OUT=1;
	static private boolean enabled=true; 
	private int state;
	final int VERTICAL=0, HORIZONTAL=1;
	private String name;
	private int snap2SideNum = 0, shiftOnSide=0, occupyOnSide, slideOut;
	private int buttonOnSideNum = 0;
	public int width=200, height=200, extWidth, extHeight, top=0, left=0, extLeft, extTop, buttonShift=20;
	private boolean hidden=true;
	private int buttonLeft, buttonTop, buttonWidth=20, buttonHeight=height; 
	private int contentLeft, contentTop, contentWidth=width, contentHeight=height; 
	private float textRot;
	
	private PImage slideIcon;
	private int iconToggle=0;
	
	CampusMap applet;
	PFont myFont;
	
	int[][] buttonFill;
	int[][] buttonOverFill;
	
	// Timing for sliding
	private final int SLIDE_DURATION=1200;
	private int slideBeginTime=0;
	private int toPos, urPos, slideDistance, currOrigin;
	
	private boolean sliding;
	private boolean requestForSlide=false;
	private int dirOnScreen;
	private int slideDir=1;
	
	private boolean mouseOverThis;
	private boolean wasAlreadyOnThis=false;
	private boolean mousePressed;
	private boolean buttonOver;
	private boolean oldButtonOver;
	private boolean buttonComponentChanged=true; //initial draw
	private boolean contentComponentChanged=true;
	private boolean wholeSlideCaseChanged=true;
	int mousePressNum=0;
	
	private boolean initialDraw=true;

	
	
	public SlideCase(CampusMap p_applet, String p_name, int side, int shift, 
			int p_occupyOnSide, int p_slideOut){
		applet= p_applet;
		applet.env.objectInitDisplay.setText("SlideCases");
		name=p_name;
		state=SHIFTED_IN;
		mousePressed=true;
		mouseOverThis=false;
		
		snap2SideNum=side;
		shiftOnSide = shift;
		occupyOnSide= p_occupyOnSide;
		slideOut=p_slideOut;

		sliding = false;
		slideIcon=loadImage(Environment.address+Environment.ressourceFolder+"arrow.gif");
		
		// Alignments
		calcAlignment();

		content = new Vector();
		
		//System.out.println("buttonTop"+buttonTop+", buttonHeight"+buttonHeight+", buttonWidth"+buttonWidth+", buttonLeft"+buttonLeft);
		buttonFill = new int[buttonHeight][buttonWidth];
		buttonOverFill = new int[buttonHeight][buttonWidth];
		float multiplier = 255/ buttonWidth;
		System.out.println("buttonFill[0].length: "+buttonFill[0].length);

		for (int i=0; i<buttonFill.length; i++) { 
		  for(int j=0; j<buttonFill[i].length; j++) { 
		    int c = color( 200-(j*multiplier)/2 , 150, 150); 
		    buttonFill[i][j] =c; 
		  } 
		} 
		for (int i=0; i<buttonFill.length; i++) { 
		  for(int j=0; j<buttonFill[i].length; j++) { 
		    int c = color( 255-((j*multiplier)*2)/3 , 150, 150); 
		    buttonOverFill[i][j] =c; 
		  } 
		} 
		buttonOver=false;
	}
	
	public void calcAlignment(){
		if(snap2SideNum==LEFT || snap2SideNum==RIGHT){
			top = shiftOnSide;
			dirOnScreen=HORIZONTAL;
			width=slideOut;
			height=occupyOnSide;
			contentWidth=width-buttonWidth;
			contentHeight=height;
			contentTop=top;
			if(snap2SideNum==LEFT){
				left=buttonShift-width;
				toPos=0;
				buttonOnSideNum=RIGHT;
				contentLeft=left;
			}
			if(snap2SideNum==RIGHT){
				left=applet.width-buttonShift;
				toPos=applet.width-width;
				buttonOnSideNum=LEFT;
				contentLeft=left+buttonWidth;
			}
			urPos=left;
			textRot=PI/2;
		}
		if(snap2SideNum==BOTTOM || snap2SideNum==TOP){
			left = shiftOnSide;
			dirOnScreen=VERTICAL;
			width=occupyOnSide;
			height=slideOut;
			contentWidth=width;
			contentHeight=height-buttonHeight;
			contentLeft=left;
			if(snap2SideNum==BOTTOM){
				top=applet.height-buttonShift;
				toPos=applet.height-height;
				buttonOnSideNum=TOP;
				buttonTop=top+buttonHeight;
			}
			if(snap2SideNum==TOP){
				top=-(height-buttonShift);
				toPos=0;
				buttonOnSideNum=BOTTOM;
				buttonTop=height;
			}
			urPos=top;
			textRot=0;
		}
		extLeft=left;
		extTop=top;
		slideDistance= toPos-urPos;

		switch(buttonOnSideNum){
		case LEFT:
			buttonLeft=0;
			buttonTop=0;
			buttonHeight=height;
			break;
		case RIGHT:
			buttonLeft=width-buttonShift;
			buttonTop=0;
			buttonHeight=height;
			break;
		case BOTTOM:
			buttonLeft=0;
			buttonTop=height-buttonShift;
			buttonHeight=buttonWidth;
			buttonWidth=width;
			break;
		case TOP:
			buttonLeft=0;
			buttonTop=0;
			buttonHeight=buttonWidth;
			buttonWidth=width;
			break;
		}
	}
	
	public void setup(){
		loadPixels();
		size(width, height, P3D);
		//myFont = createFont(Environment.address+Environment.ressourceFolder+"Genetrix.otf", 10);
		noLoop();
	}
	
	public static void setActive(boolean p_enabled){
		enabled=p_enabled;
	}
	
	public void addContent(GuiObject[] obj){
		for(int addIndex=0;addIndex<obj.length;addIndex++)content.add(obj[addIndex]);
	}
	
	public int getWidth(){
		return extWidth;
	}
	public int getRealWidth(){
		return width;
	}
	public int getRealHeight(){
		return height;
	}
	public String getName(){
		return name; 
	}
	public FVector getPos(){
		float[] back = {extLeft, extTop};
		return new FVector(back);
	}
	public PImage getPixels(){
		//width=content.width+30;
		//height=content.height+30;
		//calcAlignment();
		
		extWidth=width;
		int lineAmount=height;
		//int[] bufferInt = new int[pixels.length];
		PImage bufferImage = new PImage(pixels, width, height, RGB);

		extLeft=left;
		extTop=top;
		if(left!=urPos)hidden=false;
		else hidden=true;
		
		return bufferImage;
	}

	public void evalMouse(int mouse_x, int mouse_y, boolean p_mouse_pressed, boolean p_mouse_released){
		if(!sliding && enabled){
			// genral mouseover for disabling the rest
			if( mouse_x-left>0 && mouse_x-left<width && 
				mouse_y-top>0 && mouse_y-top<height){
				// evoke mouse over
				mouseover(mouse_x, mouse_y, p_mouse_pressed, p_mouse_released);
			}else mouseOut();
			// state change check
			if(buttonOver!=oldButtonOver)buttonComponentChanged=true;
			oldButtonOver=buttonOver;
			
			// autonomous content check
			//**********************
			for(int contentIndex=0; contentIndex<content.size();contentIndex++)
				contentComponentChanged = ((GuiObject)content.get(contentIndex)).check(mouse_x-left, mouse_y-top, p_mouse_pressed, p_mouse_released);

		}
	}
	public void mouseover(int mouse_x, int mouse_y, boolean p_mouse_pressed, boolean p_mouse_released){
		if(enabled){
			applet.controls.setThreeDeeControlEnabled(false);
			//System.out.println("mouse currently pressed: "+p_mouse_pressed+"mouse pressed brefore: "+mousePressed);
			if(p_mouse_pressed){
				// if in the drawing BEFORE the mouse was already over this and NOT pressed...
				if(!mousePressed && mouseOverThis){
					if(buttonOver)mouseClick();
				}
				mousePressed=true;
			}else mousePressed=false;
			GuiObject.setIsInUse(true);
			mouseOverThis=true;
			//button
			//**********************
			if( mouse_x-left>buttonLeft && mouse_x-left<buttonLeft+buttonWidth && 
				mouse_y-top>buttonTop 	&& mouse_y-top<buttonTop+buttonHeight)
				buttonOver=true;
			else buttonOver=false;
		}
	}
	
	public void mouseClick(){
		slide();
	}
	
	public void mouseOut(){
		// ONLY when mouse leaves THIS slideCase
		if(mouseOverThis){
			//.. set Global control disable 
			GuiObject.setIsInUse(false);
			mouseOverThis=false;
			applet.controls.setThreeDeeControlEnabled(true);
		}
		// reset flags
		buttonOver=false;
		mousePressed=false;
	}
	
	public void slide(){
		slideBeginTime=applet.millis();
		currOrigin=currOrigin==urPos?toPos:urPos;
		state=state==SHIFTED_IN?SHIFTED_OUT:SHIFTED_IN;
		sliding=true;
	}
	
	public void requestSlide(){
		requestForSlide=(requestForSlide?false:true);
	}
	
	public int getState(){
		return state;
	}
	
	public boolean isSliding(){
		return sliding;
	}
	
	//calculates the current position in the current animation
	private int updatePos(int p_val) {
		int val=p_val;
		int timePassed = applet.millis() - slideBeginTime;
		if ( timePassed > SLIDE_DURATION) {
			val = currOrigin + slideDistance*slideDir;
			slideBeginTime = 0;
			slideDir*=-1;
			sliding=false;
			if(slideIcon!=null){
				try{
					slideIcon= swapImage(slideIcon);
				}catch(Throwable e){
					System.out.println("Slidecase icon wird noch geladen...");
				}
				iconToggle=iconToggle==0?1:0;
			}
			if(state==SHIFTED_IN)
				for(int contentIndex=0; contentIndex<content.size();contentIndex++)
					((GuiObject)content.get(contentIndex)).setActive(false);
			
			// new Slide if requested
			if(requestForSlide){
				slide();
				requestForSlide=false;
			}
		}
		else {
			float timePassedOfTotal = ((float)timePassed) / ((float)SLIDE_DURATION);
			timePassedOfTotal = FMath.calcAcceleration(timePassedOfTotal, 1);
			val = (int)((float)currOrigin + slideDistance*((float)slideDir*timePassedOfTotal));
			//System.out.println("timePassedOfTotal "+timePassedOfTotal+" slideDistance "+slideDistance+" val: "+val);
		}
		return val;
	}

	public void draw(){
		// slide update
		if(sliding)
			if(dirOnScreen==VERTICAL)
				this.top=updatePos(this.top);
			else if(dirOnScreen==HORIZONTAL)
				this.left=updatePos(this.left);
		
		//Content changed? - draw all
		if(contentComponentChanged && state!=SHIFTED_IN || wholeSlideCaseChanged){
			drawContent();
			drawButtonPane();
			contentComponentChanged=false;
		// only buttonover? - draw only button
		}else if(buttonComponentChanged){
			drawButtonPane();
			buttonComponentChanged=false;
		}
		
		if(initialDraw){
			drawButtonPane();
			initialDraw=false;
		}

		stroke(255);
		rect(contentLeft+2, contentTop+2, contentWidth-2, contentHeight-2);
		if(wholeSlideCaseChanged)updatePixels();
	}
	
	private void drawButtonPane(){
		int size = 20;
		
		noFill();
		// buttonFill
		//***************************************
		for (int i=0; i<buttonFill.length;i++) 
			for(int j=0; j<buttonFill[i].length;j++) 
			    set(j+buttonLeft, i+buttonTop, (buttonOver?buttonOverFill[i][j]:buttonFill[i][j])); 
		
		//icon and font
		//***************************************
		if(slideIcon!=null){
			image(slideIcon, buttonLeft, buttonTop);
		}
		//else slideIcon=loadImage(Environment.address+Environment.ressourceFolder+"arrow.gif");

		if(applet.myFont!=null){
			pushMatrix();
			if(snap2SideNum==LEFT || snap2SideNum==RIGHT){
				translate(buttonLeft+5,buttonTop+20,0);
				rotate((float)PI/2);
			} else if(snap2SideNum==TOP || snap2SideNum==BOTTOM){
				translate(buttonLeft+20,buttonTop+13,0);
			}
			fill(246, 244, 189);
			textFont(applet.myFont, 14);
			text(name, 0, 0);
			popMatrix();
		}else {
			applet.myFont = loadFont(Environment.address+Environment.ressourceFolder+"CenturyGothic-14.vlw.gz");
		}
		
		// global hasChanged
		//*****************************************
		wholeSlideCaseChanged=true;
	}
	
	private void drawContent(){
		//background
		//***************************************
		background(240, 240, 255);
		//fill(246, 244, 189);
		
		for(int contentIndex=0; contentIndex<content.size();contentIndex++)
			((GuiObject)content.get(contentIndex)).draw(this);
		
		
		// global hasChanged
		//*****************************************
		wholeSlideCaseChanged=true;
	}

	public PImage swapImage(PImage img)throws Throwable{
		PImage bufferImage = new PImage(img.width, img.height);
		bufferImage.format=ARGB;
		for(int backIndex=img.width, 
				runIndex=0, 
				lineIndex=0; lineIndex*img.width+runIndex<img.pixels.length;backIndex--, runIndex++){
			int tempPixel = img.pixels[lineIndex*img.width+backIndex];
			bufferImage.pixels[lineIndex*img.width+runIndex]=tempPixel;
			//System.out.println("indeces: "+backIndex+", "+runIndex+", "+lineIndex+", "+tempPixel);
			if(backIndex<=0){
				lineIndex++;
				runIndex=0;
				backIndex=img.width;
			}
		}
		return bufferImage;
	}
}