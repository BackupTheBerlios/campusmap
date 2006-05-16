package CampusMap;

import java.util.Vector;

import processing.core.PApplet;

public class GuiManager extends Vector{
	private int			counter;	//the counter for generating the IDs, increased everytime add() is called
	
	public GuiManager(){
		counter		= 0;
	}
	
	private int findGuiObject(int id) {
		int index = -1;
		for (int i=0; i<size(); i++) {
			if ( ((GuiObject)(elementAt(i))).getId() == id) {
				index = i;
				break;
			}
		}
		return index;
	}
	public boolean removeId(int guiObjectId) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			remove(index);
			return true;
		}
		return false;
	}
	
	public void add(GuiObject obj){
		obj.setId(counter++);
		addElement(obj);
	}
		
	public void setColor(int guiObjectId, int r0, int g0, int b0, int r1,int g1,int b1) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			((GuiObject)(elementAt(index))).setColor(r0, g0, b0, r1, g1, b1);
		}
		else System.err.println("GuiObjects.setColor(): guiObject id " + guiObjectId + " not available");
	}

	public void setActive(int guiObjectId, boolean _active) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			((GuiObject)(elementAt(index))).setActive(_active);
		}
		else System.err.println("GuiObjects.setActive(): guiObject id " + guiObjectId + " not available");
	}

	public boolean getActive(int guiObjectId) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			return ((GuiObject)(elementAt(index))).getActive();
		}
		else System.err.println("GuiObjects.getActive(): guiObject id " + guiObjectId + " not available");
		return false;
	}
	public void setValue(int guiObjectId, float _val, boolean _execute) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			((Slider)(elementAt(index))).setValue(_val);
			if (_execute)
				((Slider)(elementAt(index))).execute(true);
		}
		else System.err.println("GuiObjects.setValue(): guiObject id " + guiObjectId + " not available");
	}
	
	public float getValue(int guiObjectId) {
		int index = findGuiObject(guiObjectId);
		if (index != -1) {
			return ((Slider)(elementAt(index))).getValue();
		}
		//System.err.println("GuiObjects.getValue(): guiObject id " + guiObjectId + " not available");
		return 0.0f;
	}
	
	public void setGlobalActive(boolean p_active){
		for(int activeIndex=0; activeIndex<size(); activeIndex++)
			((GuiObject)get(activeIndex)).setActive(p_active);
	}

	private void evaluate(int mouseX, int mouseY, boolean mouseJustReleased, boolean mouseJustPressed){
		boolean tempIsInUse = false;
		for (int guiObjectNo = 0; guiObjectNo < size(); guiObjectNo++)
			if (((GuiObject)(elementAt(guiObjectNo))).getActive()) {
				if (((GuiObject)(elementAt(guiObjectNo))).check(mouseX, mouseY, mouseJustReleased, mouseJustPressed)) {
					tempIsInUse = true;
					System.out.println("guiObject is in use");
				}
			}
		GuiObject.isInUse = tempIsInUse;
	}

}